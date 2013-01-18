#!/bin/bash

# Variables.
TW_DIR='/tmp/trainingwheels'

echo ''
echo '======================================================================='
echo 'Copying the settings files for the controller playbooks...'
cp $TW_DIR/vagrant/controller-settings-vagrant.yml $TW_DIR/playbooks/controller/settings.yml

echo ''
echo '======================================================================='
echo 'Running controller setup...'
cd $TW_DIR/playbooks/controller
ansible-playbook -c local --tags="common,dev" --user=root setup.yml

echo ''
echo '======================================================================='
echo 'Running sample data playbooks...'
ansible-playbook -c local --user=root sample-data.yml
