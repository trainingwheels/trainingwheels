#!/bin/bash

# Setup Ansible.
sudo aptitude update
sudo aptitude -y install git python-jinja2 python-yaml python-paramiko python-software-properties
sudo add-apt-repository -y ppa:rquillo/ansible/ubuntu
sudo aptitude update
sudo aptitude install ansible
echo "localhost" > /tmp/ansible-hosts
sudo chown root: /tmp/ansible-hosts
sudo mv /tmp/ansible-hosts /etc/ansible/hosts

# Grab Training Wheels.
sudo git clone https://github.com/fourkitchens/trainingwheels.git /tmp/trainingwheels
