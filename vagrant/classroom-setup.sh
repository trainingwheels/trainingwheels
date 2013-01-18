#!/bin/bash

# Variables.
TW_DIR='/tmp/trainingwheels'

echo ''
echo '======================================================================='
echo 'Copying the settings files for the classroom playbooks...'
cp $TW_DIR/vagrant/classroom-settings-vagrant.yml $TW_DIR/playbooks/classroom/settings.yml

echo ''
echo '======================================================================='
echo 'Running classroom setup playbook...'
cd $TW_DIR/playbooks/classroom
ansible-playbook -c local --user=root setup.yml
