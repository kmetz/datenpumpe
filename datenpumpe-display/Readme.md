# Datenpumpe-Display

## Requirements
- node.js 8.11.3+

## Install
`$ npm install`

On raspbian stretch 14.04, update chromium-browser to 65:
- `wget https://launchpad.net/~canonical-chromium-builds/+archive/ubuntu/stage/+build/14482955/+files/chromium-codecs-ffmpeg_65.0.3325.181-0ubuntu0.14.04.1_armhf.deb; sudo dpkg -i chromium-codecs-ffmpeg_65.0.3325.181-0ubuntu0.14.04.1_armhf.deb`
- `wget https://launchpad.net/~canonical-chromium-builds/+archive/ubuntu/stage/+build/14482955/+files/chromium-browser_65.0.3325.181-0ubuntu0.14.04.1_armhf.deb; sudo dpkg -i chromium-browser_65.0.3325.181-0ubuntu0.14.04.1_armhf.deb`
(https://github.com/GoogleChrome/puppeteer/issues/550#issuecomment-349497339)

## Run  
`$ node server.js --serialPort=/dev/tty.usbmodem2161051`

### Args
- --serialport=/dev/ttyXYZ0
- --quiet: no logging
- --verbose: with data logging
