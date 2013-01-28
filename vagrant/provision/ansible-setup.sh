#!/bin/bash

echo ''
echo '======================================================================='
echo 'Setting up Ansible...'

if [ `which ansible` ]; then
  echo 'Ansible already installed.'
  exit;
fi

sudo aptitude -q=2 update
sudo aptitude -q=2 -y install build-essential git python-dev python-jinja2 python-yaml python-paramiko python-software-properties python-pip
sudo pip install pymongo
sudo add-apt-repository -y ppa:rquillo/ansible/ubuntu 2>&1
sudo aptitude -q=2 update
sudo aptitude -q=2 -y install ansible
echo "localhost" > /tmp/ansible-hosts
sudo chown root: /tmp/ansible-hosts
sudo mv /tmp/ansible-hosts /etc/ansible/hosts
