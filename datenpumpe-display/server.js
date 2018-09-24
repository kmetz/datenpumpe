let randomContentURL = 'https://www.wikimedia.de/extern/datenpumpe/';
const cachedContentDirs = 50;
const webServerPort = 8080;
const webSocketServerPort = 8081;

const argv = require('minimist')(process.argv.slice(2));
const pumpLevelSerialPort = ('serialPort' in argv) ? argv.serialPort : '/dev/ttyACM0';
const loglevel = ('quiet' in argv) ? 0 : (('verbose' in argv) ? 2 : 1);

const puppeteer = require('puppeteer');
// Don't use bundled chrome on raspbian / arm
const puppeteerLaunchOptions = (process.platform === 'linux' && process.arch === 'arm') ? {executablePath: '/usr/bin/chromium-browser'} : {};

const webSocketServer = require('websocket').server;
const http = require('http');

const express = require('express');
const webServer = express();
const execSync = require('child_process').execSync;
const exec = require('child_process').exec;
const uuidv1 = require('uuid/v1');

let isOffline = false;


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



// Create content (website screenshots) ------------------

function downloadContent(count) {
  let filename = uuidv1() + '.png';
  console.log('Downloading new content: ' + filename + ' ...');

  let browser = {};
  (async () => {
    browser = await puppeteer.launch(puppeteerLaunchOptions);
    const page = await browser.newPage();
    await page.setViewport({width: 1024, height: 1024});
    const navigationPromise = page.waitForNavigation({timeout: 60000, waitUntil: 'networkidle0'});
    await page.goto(randomContentURLparsed.href);
    await navigationPromise;
    await page.screenshot({path: __dirname + '/content/' + filename});
    await browser.close();
  })()
    .then(() => {
      isOffline = false;
      webServer.use('/content/' + filename, express.static(__dirname + '/content/' + filename));
      console.log('Download succeeded: ' + filename);
      // Delete oldest when more than cachedContentDirs exist
      execSync('cd ' + __dirname + '/content/' + '; ls -t | sed -e "1,' + cachedContentDirs + 'd" | xargs rm -rf');

      // Sequentially download multiple
      if (count) {
        downloadContent(count - 1);
      }
    })
    .catch((error) => {
      isOffline = true;
      console.log('Error downloading ' + filename + ': ' + error);
      browser.close();

      // Try again
      if (count) {
        window.setTimeout(() => {
          downloadContent(count);
        }, 10000);
      }
    });
}

// Enshure we have enough content
exec('cd ' + __dirname + '/content;' + 'ls -d1 *.png | wc -l', (err, stdout) => {
  if (!err) {
    let existing = parseInt(stdout.trim());
    downloadContent(cachedContentDirs - existing);
  }
});


// Web server ---------------------------------------------------------------

log('Content dir: ' + __dirname + '/content/');

// Serve client at /
webServer.use(express.static(__dirname + '/client'));
// Serve content
webServer.use('/content', express.static(__dirname + '/content'));

// Redirects from /content to actual content.
webServer.get('/content', (req, res) => {
  log('GET /content');
  //// Pick random content when offline, or newest.
  //// let lsCommand = (isOffline ? 'ls -d1 *.png | sort -R | head -n 1' : 'ls -td1 *.png | head -n 1');
  // Always pick random content
  let lsCommand = 'ls -d1 *.png | sort -R | head -n 1';

  exec('cd ' + __dirname + '/content;' + lsCommand, (err, stdout) => {
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
  downloadContent(1);
});


webServer.listen(webServerPort, () => {
  log('Web server listening on port ' + webServerPort + '.');
});


// Send pump level from serial port to client via WebSocket -----------------

let connection = {};
let pumpLevel = 0;
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
