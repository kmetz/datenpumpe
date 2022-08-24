(function () {

  console.log('Hello');

  const pumpLevelMin = 10;
  const pumpLevelMax = 300;

  const contentURL = '/content';
  const main = document.querySelector('#main');
  const content = document.querySelector('#content');
  const overlay = document.querySelector('#overlay');
  const messageDiv = document.querySelector('#message');
  let pumpLevel = 0;
  let reloaded = true;
  let lastPumpLevel = 0, visibility = 0.0;
  let wasRaising = false, isRaising = false, strokeWasStarted = false;
  let lastWave = {};

  const keyboardSteps = 10;
  const keyboardChangeAmount = ((pumpLevelMax - pumpLevelMin) / keyboardSteps);


  content.src = contentURL;

  window.WebSocket = window.WebSocket || window.MozWebSocket;
  const connection = new WebSocket('ws://127.0.0.1:8081');

  connection.onopen = () => {
    console.log('Connection open.')
  };

  connection.onerror = (error) => {
    console.log('Connection error.')
  };


  // When data arrives from server.
  connection.onmessage = (message) => {
    // console.log('Received message: ' + message.data)
    pumpLevel = Number.parseInt(message.data);
    updateScreen();
  }


  // Keyboard support (for debugging).
  // [P]: raise Pressure
  // [L]: Lower pressure
  // Esc: pressure 0
  document.addEventListener('keypress', event => {
    if (event.code === 'KeyP') {
      pumpLevel = Math.min(pumpLevel + keyboardChangeAmount, pumpLevelMax);
      updateScreen();
    }
    if (event.code === 'KeyL') {
      pumpLevel = Math.max(pumpLevel - keyboardChangeAmount, pumpLevelMin);
      if (pumpLevel < keyboardChangeAmount) {
        pumpLevel = 0;
      }
      updateScreen();
    }
    if (event.code === 'Escape') {
      pumpLevel = 0;
      updateScreen();
    }
  })


// Should be called after each pumpLevel change.
  function updateScreen() {
    wasRaising = isRaising;
    isRaising = (pumpLevel > lastPumpLevel);

    if (!Number.isInteger(pumpLevel) || pumpLevel === lastPumpLevel) {
      return;
    }
    lastPumpLevel = pumpLevel;
    visibility = Math.max(0, Math.min(pumpLevel, pumpLevelMax) - pumpLevelMin) / (pumpLevelMax - pumpLevelMin);

    // Reload content when below pumpLevelMin.
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

    // Animate message
    messageDiv.style.opacity = (pumpLevel < pumpLevelMin) ? '1' : '0';

    // Animate circle.
    overlay.style.boxShadow = 'inset 0 0 0 ' + (1073 * (1 - visibility)) + 'px black';

    // Animate content.
    content.style.opacity = EasingFunctions.easeOutQuad(visibility);
    //content.style.transform = 'scale(' + ((visibility/2) + 0.5) + ')';
    content.style.transform =
      'translateY(' + (450 * (1 - EasingFunctions.easeOutCubic(visibility))) + 'px) ' +
      // 'translateX(' + (53 * (1 - EasingFunctions.easeOutCubic(visibility))) + 'px) ' +
      'scale(' + Math.min(1, EasingFunctions.easeOutQuint(visibility) + 0.1) + ')';


    // Add waves.
    if (strokeWasStarted) {
      strokeWasStarted = false;
      lastWave.style.transform = 'scale(1)';
      lastWave.style.opacity = '0.0';
      window.setTimeout((wave) => {
        wave.parentNode.removeChild(wave)
      }, 3000, lastWave);
    }
    if (isRaising && !wasRaising) {
      let wave = document.createElement('div');
      wave.className = 'wave';
      wave.style.transform = 'scale(0)';
      wave.style.opacity = '0.6';
      main.appendChild(wave);
      lastWave = wave;
      strokeWasStarted = true;
    }
  };


  const EasingFunctions = {
    // no easing, no acceleration
    linear: (t) => {
      return t
    },
    // accelerating from zero velocity
    easeInQuad: (t) => {
      return t * t
    },
    // decelerating to zero velocity
    easeOutQuad: (t) => {
      return t * (2 - t)
    },
    // acceleration until halfway, then deceleration
    easeInOutQuad: (t) => {
      return t < .5 ? 2 * t * t : -1 + (4 - 2 * t) * t
    },
    // accelerating from zero velocity
    easeInCubic: (t) => {
      return t * t * t
    },
    // decelerating to zero velocity
    easeOutCubic: (t) => {
      return (--t) * t * t + 1
    },
    // acceleration until halfway, then deceleration
    easeInOutCubic: (t) => {
      return t < .5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1
    },
    // accelerating from zero velocity
    easeInQuart: (t) => {
      return t * t * t * t
    },
    // decelerating to zero velocity
    easeOutQuart: (t) => {
      return 1 - (--t) * t * t * t
    },
    // acceleration until halfway, then deceleration
    easeInOutQuart: (t) => {
      return t < .5 ? 8 * t * t * t * t : 1 - 8 * (--t) * t * t * t
    },
    // accelerating from zero velocity
    easeInQuint: (t) => {
      return t * t * t * t * t
    },
    // decelerating to zero velocity
    easeOutQuint: (t) => {
      return 1 + (--t) * t * t * t * t
    },
    // acceleration until halfway, then deceleration
    easeInOutQuint: (t) => {
      return t < .5 ? 16 * t * t * t * t * t : 1 + 16 * (--t) * t * t * t * t
    }
  }

})();
