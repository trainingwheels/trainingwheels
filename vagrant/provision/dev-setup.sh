#!/bin/bash

# Variables.
TW_DIR='/var/trainingwheels'

echo ''
echo '======================================================================='
echo 'Setting up the Training Wheels developer environment...'
cd $TW_DIR/vagrant/provision
ansible-playbook -c local --user=root dev-setup-playbook.yml
