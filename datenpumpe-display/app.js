var randomContentURL = 'https://de.m.wikipedia.org/wiki/Spezial:Zuf%C3%A4llige_Seite#/random';
var cachedContentDirs = 10;

var webServerPort = 8080;
var webSocketServerPort = 8081;
var pumpLevelSerialPort = '/dev/tty.usbmodem2161051';

// Web server ---------------------------------------------------------------

var express = require('express');
var webServer = express();
const execSync = require('child_process').execSync;
const exec = require('child_process').exec;
const uuidv1 = require('uuid/v1');
var isOffline = false;

console.log('Content dir: '+ __dirname + '/content/');

// Serve client at /
webServer.use(express.static(__dirname + '/client'));
// Serve content
webServer.use('/content', express.static(__dirname + '/content'));

// Redirects from /content to actual content.
webServer.get('/content', function(req,res) {
  console.log('GET /content');
  // Pick random content when offline, or newest.
  exec(isOffline ? 'cd content; ls -d1 R_* | sort -R | head -n 1' : 'cd content; ls -td1 R_* | head -n 1',
    (err, stdout, stderr) => {
    if (!err && stdout.trim().length > 0) {
      var location =  '/content/' + stdout.trim();
      res.redirect(302, location);
      console.log('302 ––> ' + location);
    }
    else {
      console.log('Error: no content available.');
      res.status(503).send('No content available, please enshure internet connection and try again shortly.');
    }
  });

  // Download new content (try to)
  var uuid = uuidv1();
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
  console.log('Web server listening on port ' + webServerPort + '.');
});



// Send pump level from serial port to client via WebSocket -----------------

var connection = {};
var pumpLevel = 0;

var webSocketServer = require('websocket').server;
var http = require('http');
var wsHttpServer = http.createServer(function(request, response) {
});
wsHttpServer.listen(webSocketServerPort, () => {});
var socketServer = new webSocketServer({
  httpServer: wsHttpServer
});

socketServer.on('request', (request) => {
  console.log((new Date()) + ' WebSocket connection from origin ' + request.origin + '.');
  connection = request.accept(null, request.origin);
  console.log((new Date()) + ' WebSocket connection accepted.');
});


var SerialPort = require('serialport');
var serial = new SerialPort(pumpLevelSerialPort, { baudRate: 9600 });
serial.on('error', function(err) {
  console.log('Error: ', err.message)
});

const Readline = require('@serialport/parser-readline')
const parser = serial.pipe(new Readline())

parser.on('data', (data) => {
  // console.log('   ' + data);
  if (Object.keys(connection).length == 0) return;
  split = data.split(':');
  if (split.length == 2) {
    pumpLevel = Number.parseInt(split[1].trim());
     if (Number.isInteger(pumpLevel)) {
      connection.sendUTF(pumpLevel.toString());
      // console.log('–> ' + pumpLevel.toString());
    }
  }
});
