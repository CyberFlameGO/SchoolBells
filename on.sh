#!/bin/bash
# sends on signal to the USB relay
echo -e "\xFF\x01\x01" > /dev/ttyUSB0


