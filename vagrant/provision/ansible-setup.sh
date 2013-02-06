#!/bin/bash

echo ''
echo '======================================================================='
echo 'Setting up Ansible...'

if [ `which ansible` ]; then
  echo 'Ansible already installed.'
  exit;
fi

aptitude -q=2 update
aptitude -q=2 -y install build-essential git python-dev python-jinja2 python-yaml python-paramiko python-software-properties python-pip debhelper python-support cdbs

git clone https://github.com/ansible/ansible.git /tmp/ansible
cd /tmp/ansible
git checkout v0.9 2>&1
make deb 2>&1
cd /tmp
dpkg -i ansible_0.9_all.deb

echo "localhost" > /etc/ansible/hosts
