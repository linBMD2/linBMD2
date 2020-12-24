#!/bin/bash
#
# this script will start the linBMD2 system
#
cd /home/hwright/linBMD2

php spark serve &

sleep 2

firefox --new-window http://localhost:8080/home &

exit
