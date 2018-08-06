(function() {

console.log('Hello')


var pumpLevelMin = 20;
var pumpLevelMax = 300;
var contentURL = '/content';

///

var pumpLevel = 0;
var reloaded = true;
var content = document.querySelector('#content');
var overlay = document.querySelector('#overlay');
var visibility = 0.0;

content.src = contentURL;

window.WebSocket = window.WebSocket || window.MozWebSocket;
var connection = new WebSocket('ws://127.0.0.1:8081');

connection.onopen = function () {
  console.log('Connection open.')
};

connection.onerror = function (error) {
  console.log('Connection error.')
};


connection.onmessage = function (message) {
  // console.log('Received message: ' + message.data)

  pumpLevel = Number.parseInt(message.data);
  if (Number.isInteger(pumpLevel) && pumpLevel > 0 && pumpLevel <= pumpLevelMax) {

    if (pumpLevel < pumpLevelMin) {
      if (!reloaded) {
        content.src = ''; // ?
        content.src = contentURL;
        reloaded = true;
      }
    }
    else {
      reloaded = false;
    }
    visibility = Math.max(0, pumpLevel - pumpLevelMin) / (pumpLevelMax - pumpLevelMin);
    content.style.opacity = visibility;
    overlay.style.boxShadow = 'inset 0 0 10px ' + (300 * (1 - visibility)).toString() + 'px rgb(5,200,255)'

    // console.log('–> ' + content.style.opacity);
  }
}

// connection.onclose()

})();
