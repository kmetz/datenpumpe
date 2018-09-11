let randomContentURL = 'http://www.mtz.in/datenpumpe-server/';
const cachedContentDirs = 50;
const webServerPort = 8080;
const webSocketServerPort = 8081;

const argv = require('minimist')(process.argv.slice(2));
const pumpLevelSerialPort = ('serialPort' in argv) ? argv.serialPort : '/dev/ttyACM0';
const loglevel = ('quiet' in argv) ? 0 : (('verbose' in argv) ? 2 : 1);


// Read location query setting from txt file (~/Desktop/location.txt).
const {URL} = require('url');
let randomContentURLparsed = new URL(randomContentURL);
const fs = require('fs');
const locationTxtPath = require('os').homedir() + '/Desktop/location.txt';
let location = '';
try {
  location = fs.readFileSync(locationTxtPath).toString().replace(/[^a-z0-9_-]/gmi, '');
} catch (e) {
  log('Location: ' + e.message);
}
if (location.length) {
  log('Location: ' + location);
  randomContentURLparsed.search = 'location=' + location;
}
log('Content URL: ' + randomContentURLparsed.href);


// Web server ---------------------------------------------------------------

const express = require('express');
const webServer = express();
const execSync = require('child_process').execSync;
const exec = require('child_process').exec;
const uuidv1 = require('uuid/v1');
const puppeteer = require('puppeteer');
let isOffline = false;

log('Content dir: ' + __dirname + '/content/');

// Serve client at /
webServer.use(express.static(__dirname + '/client'));
// Serve content
webServer.use('/content', express.static(__dirname + '/content'));

// Redirects from /content to actual content.
webServer.get('/content', (req, res) => {
  log('GET /content');
  // Pick random content when offline, or newest.
  exec('cd ' + __dirname + '/content;' + (isOffline ? 'ls -d1 *.png | sort -R | head -n 1' : 'ls -td1 *.png | head -n 1'),
    (err, stdout) => {
      if (!err && stdout.trim().length > 0) {
        let location = '/content/' + stdout.trim();
        res.redirect(302, location);
        log('Redirected to ' + location);
      }
      else {
        log('Error: no content available.');
        res.status(503).send('No content available, please ensure internet connection and try again shortly.');
      }
    });


  // Add new content (try to)
  let filename = uuidv1() + '.png';
  console.log('Downloading new content: ' + filename + ' ...');

  (async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    //await page.waitForSelector('body.screenshot-ready', {timeout: 120000});
    //await page.waitForSelector('#logo', {hidden: true, timeout: 180000});
    await page.setViewport({width: 1024, height: 1024});
    const navigationPromise = page.waitForNavigation({timeout: 60000, waitUntil: 'networkidle0'});
    await page.goto(randomContentURLparsed.href);
    await navigationPromise;
    await page.screenshot({path: __dirname + '/content/' + filename});
    await browser.close();

    isOffline = false;
    webServer.use('/content/' + filename, express.static(__dirname + '/content/' + filename));
    console.log('Download succeeded: ' + filename);
    // Delete oldest when more than cachedContentDirs exist
    execSync('cd ' + __dirname + '/content/' + '; ls -t | sed -e "1,' + cachedContentDirs + 'd" | xargs rm -rf');
  })().catch((error) => {
    isOffline = true;
    console.log('Error downloading ' + filename + ': ' + error);
    console.log('Using offline mode for next request.');
  });
});



webServer.listen(webServerPort, () => {
  log('Web server listening on port ' + webServerPort + '.');
});


// Send pump level from serial port to client via WebSocket -----------------

let connection = {};
let pumpLevel = 0;

const webSocketServer = require('websocket').server;
const http = require('http');
const wsHttpServer = http.createServer((request, response) => {
});
wsHttpServer.listen(webSocketServerPort, () => {
});
const socketServer = new webSocketServer({
  httpServer: wsHttpServer
});

socketServer.on('request', (request) => {
  log('WebSocket connection from origin ' + request.origin + '.');
  connection = request.accept(null, request.origin);
  log('WebSocket connection accepted.');
});


const SerialPort = require('serialport');
const serial = new SerialPort(pumpLevelSerialPort, {baudRate: 9600});
serial.on('error', function (err) {
  log(err.message)
});

const Readline = require('@serialport/parser-readline');
const parser = serial.pipe(new Readline());

parser.on('data', (data) => {
  if (Object.keys(connection).length === 0) return;
  split = data.split(':');
  if (split.length === 2) {
    pumpLevel = Number.parseInt(split[1].trim());
    if (Number.isInteger(pumpLevel)) {
      connection.sendUTF(pumpLevel.toString());
      log('–> ' + pumpLevel.toString(), 2);
    }
  }
});


// Helpers ----------------------------------

function log(message, level = 1) {
  if (!loglevel || level > loglevel) return;
  loglevel <= 1 ? console.log(message) : console.info(message);
}
