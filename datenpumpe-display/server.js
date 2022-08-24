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
const uuid = require('uuid');

let lastDownloadFailed = false;
let lastQueryId = false;
let lastFilename = false;

log('----- Starting server -----');

// Assemble content URL.
const {URL} = require('url');
let randomContentURLparsed = new URL(randomContentURL);
let searchParams = new URLSearchParams();
if (lastQueryId) {
  searchParams.append('last', lastQueryId);
}

// Read location query setting from txt file (~/Desktop/location.txt).
// Note: currently unused.
let location = '';
const fs = require('fs');
const locationTxtPath = require('os').homedir() + '/Desktop/location.txt';
try {
  location = fs.readFileSync(locationTxtPath).toString().replace(/[^a-z0-9_-]/gmi, '');
} catch (e) {
  log('Location: ' + e.message);
}
if (location.length) {
  log('Location: ' + location);
  searchParams.append('location', location);
}

randomContentURLparsed.search = searchParams.toString();
log('Content URL: ' + randomContentURLparsed.href);


// Create content (website screenshots) ------------------

function downloadContent(count) {
  let filename = uuid.v1() + '.png';
  log('Downloading new content: ' + filename + ' ...');

  let browser = {};
  let queryId = false;

  (async () => {
    browser = await puppeteer.launch(puppeteerLaunchOptions);
    const page = await browser.newPage();
    let error = false;

    // Detect errors coming from embedded js from query.wikidata.org.
    page.on('console', (msg) => {
      if (msg._type === 'error' && msg._location.url.startsWith('https://query.wikidata.org/sparql?')) {
        error = 'remote js error';
      }
    });

    const loadPageContent = page.waitForNavigation({
      timeout: 60000,
      waitUntil: 'networkidle0'
    });

    await page.setViewport({width: 1024, height: 1024});
    await page.setUserAgent('Datenpumpe (display)');

    const response = await page.goto(randomContentURLparsed.href);
    await loadPageContent;
    if (response.headers().hasOwnProperty('x-datenpumpe-query-id')) {
      queryId = response.headers()['x-datenpumpe-query-id'];
    }
    if (response.status() !== 200) {
      error = 'HTTP error: ' + response.status();
    }

    if (!error) {
      await page.screenshot({path: __dirname + '/content/' + filename});
    }
    await browser.close();

    // Throw errors only after browser.close().
    if (error) {
      throw error;
    }
  })()
  .then(() => {
    lastDownloadFailed = false;
    lastQueryId = queryId;
    webServer.use('/content/' + filename, express.static(__dirname + '/content/' + filename));
    log('Download succeeded (query ID ' + queryId + '): ' + filename);

    // Delete oldest when more than cachedContentDirs exist
    execSync('cd ' + __dirname + '/content/' + '; ls -t | sed -e "1,' + cachedContentDirs + 'd" | xargs rm -rf');

    // Sequentially download multiple
    if (count > 1) {
      downloadContent(count - 1);
    }
  })
  .catch((error) => {
    lastDownloadFailed = true;
    lastQueryId = queryId;
    log('Error downloading (query ID ' + queryId + '): ' + filename + ': ' + error);
  })
}

// Ensure we have enough content
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
  // Pick random content when offline, or newest.
  let lsCommand = (lastDownloadFailed
    ? 'ls -d1 *.png | grep -vxF "' + lastFilename + '" | sort -R | head -n 1'
    : 'ls -td1 *.png | head -n 1'
  );

  exec('cd ' + __dirname + '/content;' + lsCommand, (err, stdout) => {
    if (!err && stdout.trim().length > 0) {
      let filename = stdout.trim();
      let location = '/content/' + filename;
      res.redirect(302, location);
      log('Delivered (' + (lastDownloadFailed ? 'from cache' : 'fresh') + '): ' + filename);
      lastFilename = filename;
    }
    else {
      log('Error: no content available.');
      res.status(503).send('No content available, please ensure internet connection and try again shortly.');
    }

    // Try downloading fresh content.
    downloadContent(1);
  });
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
  message = new Date().toISOString().replace('T', ' ') + ' | ' + message
  loglevel <= 1 ? console.log(message) : console.info(message);
}
