# Datenpumpe-Display

Runs on the installation itself on a Raspberry Pi 3 under Raspbian Stretch.
A server part written in node.js reads pressure data over serial from an Arduino board (Teensy).
The server part also preloads and caches content in the form of screenshots, using puppeteer (headless chromium).
A chromium browser window in kiosk mode shows the client part on the monitor.
Pressure data is pushed using a websocket from server to client.
The client renders the "spread" and animates waves with Javascript and CSS.


## Content caching and random selection
At first run, Datenpumpe-Display/Server preloads N content pages from Datenpumpe-Server into a cache.
Content is stored as screenshots in datenpumpe-display/content.
Each time the pump is used:
- A random content page from the local cache is shown.
- When an internet connection exists, the oldest page in the cache is replaced by a new random page from Datenpumpe-Server.


## Run locally

### Requirements
- node.js 8.11.3+

### Install
`$ npm install`

On raspbian stretch 14.04, update chromium-browser to 65:
- `wget https://launchpad.net/~canonical-chromium-builds/+archive/ubuntu/stage/+build/14482955/+files/chromium-codecs-ffmpeg_65.0.3325.181-0ubuntu0.14.04.1_armhf.deb; sudo dpkg -i chromium-codecs-ffmpeg_65.0.3325.181-0ubuntu0.14.04.1_armhf.deb`
- `wget https://launchpad.net/~canonical-chromium-builds/+archive/ubuntu/stage/+build/14482955/+files/chromium-browser_65.0.3325.181-0ubuntu0.14.04.1_armhf.deb; sudo dpkg -i chromium-browser_65.0.3325.181-0ubuntu0.14.04.1_armhf.deb`
(https://github.com/GoogleChrome/puppeteer/issues/550#issuecomment-349497339)

### Run  
`$ node server.js --serialPort=/dev/tty.usbmodem2161051`

### Command line arguments
- `--serialport=/dev/ttyXYZ0`  
  Port where the Teensy delivering pressure data is connected.
- `--quiet`  
  No logging.
- `--verbose`  
  With pressure data logging.
