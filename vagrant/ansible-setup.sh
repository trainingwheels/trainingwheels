#!/bin/bash

echo ''
echo '======================================================================='
echo 'Setting up Ansible...'
sudo aptitude -q=2 update
sudo aptitude -q=2 -y install git python-jinja2 python-yaml python-paramiko python-software-properties
sudo add-apt-repository -y ppa:rquillo/ansible/ubuntu
sudo aptitude -q=2 update
sudo aptitude -q=2 -y install ansible
echo "localhost" > /tmp/ansible-hosts
sudo chown root: /tmp/ansible-hosts
sudo mv /tmp/ansible-hosts /etc/ansible/hosts
