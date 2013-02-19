#!/bin/bash

# Variables.
TW_DIR='/var/trainingwheels'

echo ''
echo '======================================================================='
echo 'Copying the settings files for the controller playbooks...'
cp $TW_DIR/vagrant/provision/controller-settings-vagrant.yml $TW_DIR/playbooks/controller/settings.yml

echo ''
echo '======================================================================='
echo 'Running controller setup...'
cd $TW_DIR/playbooks/controller
ansible-playbook --sudo -c local setup.yml

echo ''
echo '======================================================================='
echo 'Running sample data playbooks...'
ansible-playbook --sudo -c local --extra-vars="remote_username=vagrant" sample-data.yml
