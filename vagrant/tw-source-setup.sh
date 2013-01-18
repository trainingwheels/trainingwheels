#!/bin/bash

# Variables.
TW_DIR='/tmp/trainingwheels'

echo ''
echo '======================================================================='
echo 'Grabbing the Training Wheels source from Github...'
ansible all -c local -s -m git -a"repo=https://github.com/fourkitchens/trainingwheels.git dest=$TW_DIR version=vagrant"
