#!/bin/bash

# Variables.
TW_DIR='/tmp/trainingwheels'

echo ''
echo 'Setting up Ansible...'
sudo aptitude update
sudo aptitude -y install git python-jinja2 python-yaml python-paramiko python-software-properties
sudo add-apt-repository -y ppa:rquillo/ansible/ubuntu
sudo aptitude update
sudo aptitude -y install ansible
echo "localhost" > /tmp/ansible-hosts
sudo chown root: /tmp/ansible-hosts
sudo mv /tmp/ansible-hosts /etc/ansible/hosts

echo ''
echo 'Grabbing the Training Wheels source from Github...'
ansible all -c local -s -m git -a"repo=https://github.com/fourkitchens/trainingwheels.git dest=/tmp/trainingwheels version=$TW_DIR"

echo ''
echo 'Copying the settings files for the controller and classroom playbooks...'
cp $TW_DIR/vagrant/classroom-settings-vagrant.yml $TW_DIR/playbooks/classroom/settings.yml
cp $TW_DIR/vagrant/controller-settings-vagrant.yml $TW_DIR/playbooks/controller/settings.yml

echo ''
echo 'Running classroom setup playbook...'
cd $TW_DIR/playbooks/classroom
ansible-playbook -c local --user=root setup.yml

echo ''
echo 'Running controller setup...'
cd $TW_DIR/playbooks/controller
ansible-playbook -c local --tags="common,dev" --user=root setup.yml

echo ''
echo 'Running sample data playbooks...'
ansible-playbook -c local --user=root sample-data.yml
