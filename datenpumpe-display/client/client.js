(function() {

console.log('Hello')

var pumpLevelMin = 20;
var pumpLevelMax = 300;

var contentURL = '/content';
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
    content.style.opacity = EasingFunctions.easeOutQuad(visibility);
    overlay.style.boxShadow =
      'black 0 0 ' + (70 * visibility) + 'px ' + (640 * (1 - visibility)) + 'px inset, ' +
      'black 0 0 0px ' + (640 * (1 - visibility)) + 'px inset';
    //content.style.transform = 'scale(' + ((visibility/2) + 0.5) + ')';
    content.style.transform = 'scale(' + EasingFunctions.easeOutCubic(visibility) + ')';

    console.log('–> ' + visibility);
  }
}

// connection.onclose()

var rotateFunction = function (t) { return (t % 0.2) - 0.1; }


var EasingFunctions = {
  // no easing, no acceleration
  linear: function (t) { return t },
  // accelerating from zero velocity
  easeInQuad: function (t) { return t*t },
  // decelerating to zero velocity
  easeOutQuad: function (t) { return t*(2-t) },
  // acceleration until halfway, then deceleration
  easeInOutQuad: function (t) { return t<.5 ? 2*t*t : -1+(4-2*t)*t },
  // accelerating from zero velocity
  easeInCubic: function (t) { return t*t*t },
  // decelerating to zero velocity
  easeOutCubic: function (t) { return (--t)*t*t+1 },
  // acceleration until halfway, then deceleration
  easeInOutCubic: function (t) { return t<.5 ? 4*t*t*t : (t-1)*(2*t-2)*(2*t-2)+1 },
  // accelerating from zero velocity
  easeInQuart: function (t) { return t*t*t*t },
  // decelerating to zero velocity
  easeOutQuart: function (t) { return 1-(--t)*t*t*t },
  // acceleration until halfway, then deceleration
  easeInOutQuart: function (t) { return t<.5 ? 8*t*t*t*t : 1-8*(--t)*t*t*t },
  // accelerating from zero velocity
  easeInQuint: function (t) { return t*t*t*t*t },
  // decelerating to zero velocity
  easeOutQuint: function (t) { return 1+(--t)*t*t*t*t },
  // acceleration until halfway, then deceleration
  easeInOutQuint: function (t) { return t<.5 ? 16*t*t*t*t*t : 1+16*(--t)*t*t*t*t }
}

})();
