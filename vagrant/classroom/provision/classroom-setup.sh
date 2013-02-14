#!/bin/bash

# Simple function to exit with a message in the case of failure.
function error_exit
{
  echo ''
  echo "$1" 1>&2
  exit 1
}

echo ''
echo '======================================================================='
echo 'Setting up the Training Wheels classroom server...'

aptitude -q=2 update > /dev/null || error_exit "Unable to update apt cache."
