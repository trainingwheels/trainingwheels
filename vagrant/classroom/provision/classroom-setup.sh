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

# Update apt so that the classroom provisioner can work (from the controller).
aptitude -q=2 update > /dev/null || error_exit "Unable to update apt cache."

# Copy the public key from the shared keypairs folder into the correct place.
cat /home/vagrant/trainingwheels/keypairs/tw.key.pub >> /home/vagrant/.ssh/authorized_keys || error_exit "Unable to insert Training Wheels key."
