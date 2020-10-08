#!/bin/bash
# sends off signal to the USB relay
echo -e "\xff\x01\x00" > /dev/ttyUSB0

