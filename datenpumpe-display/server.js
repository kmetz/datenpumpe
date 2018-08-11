const randomContentURL = 'https://de.m.wikipedia.org/wiki/Spezial:Zuf%C3%A4llige_Seite#/random';
const cachedContentDirs = 10;

const argv = require('minimist')(process.argv.slice(2));
const webServerPort = 8080;
const webSocketServerPort = 8081;
const pumpLevelSerialPort = ('serialPort' in argv) ? argv.serialPort : '/dev/tty.usbmodem2161051';

const loglevel = ('quiet' in argv) ? 0 : (('verbose' in argv) ? 2 : 1);

// Web server ---------------------------------------------------------------

const express = require('express');
const webServer = express();
const execSync = require('child_process').execSync;
const exec = require('child_process').exec;
const uuidv1 = require('uuid/v1');
let isOffline = false;

log('Content dir: '+ __dirname + '/content/');

// Serve client at /
webServer.use(express.static(__dirname + '/client'));
// Serve content
webServer.use('/content', express.static(__dirname + '/content'));

// Redirects from /content to actual content.
webServer.get('/content', function(req,res) {
  log('GET /content');
  // Pick random content when offline, or newest.
  exec('cd ' + __dirname + '/content;' + (isOffline ? 'ls -d1 R_* | sort -R | head -n 1' : 'ls -td1 R_* | head -n 1'),
    (err, stdout, stderr) => {
    if (!err && stdout.trim().length > 0) {
      let location = '/content/' + stdout.trim();
      res.redirect(302, location);
      log('Redirected to ' + location);
    }
    else {
      log('Error: no content available.');
      res.status(503).send('No content available, please enshure internet connection and try again shortly.');
    }
  });

  // Download new content (try to)
  let uuid = uuidv1();
  console.log('Downloading new content to _' + uuid + ' ...');
  exec('wget --adjust-extension --span-hosts --convert-links --no-directories --page-requisites -e robots=off '
    + '--directory-prefix=' + __dirname + '/content/_' + uuid + ' '
    + randomContentURL,
    (err, stdout, stderr) => {
    if (err) {
      console.log('Error downloading _' + uuid + ': ' + stderr);
      console.log('Using offline mode for next request.');
      isOffline = true;
      return;
    }
    isOffline = false;
    // Rename first .html to index.html
    execSync('cd ' + __dirname + '/content/_' + uuid + '; mv $(ls -1 *.html | head -n 1) index.html');
    // Mark dir as ready (R_)
    execSync('cd ' + __dirname + '/content/' + '; mv _' + uuid + ' R_' + uuid);
    webServer.use('/content/R_' + uuid, express.static(__dirname + '/content/R_' + uuid));
    console.log('Download succeeded: R_' + uuid);
    // Delete oldest when more than cachedContentDirs exist
    execSync('cd ' + __dirname + '/content/' + '; ls -t | sed -e "1,' + cachedContentDirs + 'd" | xargs rm -rf');
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
const wsHttpServer = http.createServer(function(request, response) {
});
wsHttpServer.listen(webSocketServerPort, () => {});
const socketServer = new webSocketServer({
  httpServer: wsHttpServer
});

socketServer.on('request', (request) => {
  log('WebSocket connection from origin ' + request.origin + '.');
  connection = request.accept(null, request.origin);
  log('WebSocket connection accepted.');
});


const SerialPort = require('serialport');
const serial = new SerialPort(pumpLevelSerialPort, { baudRate: 9600 });
serial.on('error', function(err) {
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
