#!/bin/bash

# Variables.
TW_DIR='/tmp/trainingwheels'

echo ''
echo '======================================================================='
echo 'Grabbing the Training Wheels source from Github...'

if [ -d $TW_DIR ]; then
  echo 'Repository already cloned, attempting to pull...'
  cd $TW_DIR
  git pull
  exit;
fi

git clone --branch vagrant https://github.com/fourkitchens/trainingwheels.git $TW_DIR
