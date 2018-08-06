#!/bin/bash

#nohup \
npm start &

#while true; do \
chromium-browser \
--no-first-run \
--start-fullscreen \
--disable-session-crashed-bubble \
--disable-infobars \
http://localhost:8080
#done

