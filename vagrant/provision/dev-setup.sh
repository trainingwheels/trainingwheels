#!/bin/bash

# Variables.
TW_DIR='/var/trainingwheels'

echo ''
echo '======================================================================='
echo 'Setting up the Training Wheels developer environment...'
cd $TW_DIR/vagrant/provision
ansible-playbook --sudo -c local dev-setup-playbook.yml --extra-vars "twdir=$TW_DIR"
