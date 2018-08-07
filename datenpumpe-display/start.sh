#!/bin/bash

npm start &

while true; do sleep 5
chromium-browser \
--no-first-run \
--start-fullscreen \
--disable-session-crashed-bubble \
--disable-infobars \
--incognito \
--kiosk \
http://localhost:8080
done
