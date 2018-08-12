(function() {

console.log('Hello');

const pumpLevelMin = 20;
const pumpLevelMax = 300;

const contentURL = '/content';
const content = document.querySelector('#content');
const overlay = document.querySelector('#overlay');
let pumpLevel = 0;
let reloaded = true;
let visibility = 0.0;

content.src = contentURL;

window.WebSocket = window.WebSocket || window.MozWebSocket;
const connection = new WebSocket('ws://127.0.0.1:8081');

connection.onopen = () => {
  console.log('Connection open.')
};

connection.onerror = (error) => {
  console.log('Connection error.')
};


connection.onmessage = (message) => {
  // console.log('Received message: ' + message.data)

  pumpLevel = Number.parseInt(message.data);
  if (Number.isInteger(pumpLevel) && pumpLevel > 0 && pumpLevel <= pumpLevelMax) {

    if (pumpLevel < pumpLevelMin) {
      if (!reloaded) {
        content.src = '';
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
      'blue 0 0 3px ' + (640 * (1 - visibility)) + 'px inset';
    //content.style.transform = 'scale(' + ((visibility/2) + 0.5) + ')';
    content.style.transform = 'scale(' + EasingFunctions.easeOutCubic(visibility) + ')';

    // console.log('–> ' + visibility);
  }
};



const EasingFunctions = {
  // no easing, no acceleration
  linear: (t) => { return t },
  // accelerating from zero velocity
  easeInQuad: (t) => { return t*t },
  // decelerating to zero velocity
  easeOutQuad: (t) => { return t*(2-t) },
  // acceleration until halfway, then deceleration
  easeInOutQuad: (t) => { return t<.5 ? 2*t*t : -1+(4-2*t)*t },
  // accelerating from zero velocity
  easeInCubic: (t) => { return t*t*t },
  // decelerating to zero velocity
  easeOutCubic: (t) => { return (--t)*t*t+1 },
  // acceleration until halfway, then deceleration
  easeInOutCubic: (t) => { return t<.5 ? 4*t*t*t : (t-1)*(2*t-2)*(2*t-2)+1 },
  // accelerating from zero velocity
  easeInQuart: (t) => { return t*t*t*t },
  // decelerating to zero velocity
  easeOutQuart: (t) => { return 1-(--t)*t*t*t },
  // acceleration until halfway, then deceleration
  easeInOutQuart: (t) => { return t<.5 ? 8*t*t*t*t : 1-8*(--t)*t*t*t },
  // accelerating from zero velocity
  easeInQuint: (t) => { return t*t*t*t*t },
  // decelerating to zero velocity
  easeOutQuint: (t) => { return 1+(--t)*t*t*t*t },
  // acceleration until halfway, then deceleration
  easeInOutQuint: (t) => { return t<.5 ? 16*t*t*t*t*t : 1+16*(--t)*t*t*t*t }
}

})();
