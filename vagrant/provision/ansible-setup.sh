#!/bin/bash

# Simple function to exit with a message in the case of failure.
function error_exit
{
  echo ''
  echo "$1" 1>&2
  exit 1
}

echo ''
echo '======================================================================='
echo 'Setting up Ansible...'

if [ `which ansible` ]; then
  echo 'Ansible already installed.'
  exit 0
fi

echo ''
echo '======================================================================='
echo 'Updating apt and installing Ansible dependencies...'
aptitude -q=2 update > /dev/null || error_exit "Unable to update apt cache."
aptitude -q=2 -y install build-essential git python-dev \
python-jinja2 python-yaml python-paramiko python-software-properties \
python-pip debhelper python-support cdbs > /dev/null 2>&1 || error_exit "Unable to install required packages."

echo ''
echo '======================================================================='
echo 'Checking out Ansible...'
mkdir /usr/local/src/ansible || error_exit "Unable to create the directory for Ansible."
git clone https://github.com/ansible/ansible.git /usr/local/src/ansible > /dev/null || error_exit "Unable to clone the git repository for Ansible."
cd /usr/local/src/ansible
git checkout v0.9 > /dev/null 2>&1 || error_exit "Unable to checkout the required tag."

echo ''
echo '======================================================================='
echo 'Building Ansible .deb file...'
make deb > /dev/null 2>&1 || error_exit "Unable to build the .deb file."
cd /usr/local/src
dpkg -i ansible_0.9_all.deb > /dev/null || error_exit "Unable to build the .deb file."
rm ansible_0.9_all.deb

echo "localhost" > /etc/ansible/hosts

echo ''
echo 'Finished Ansible setup.'
echo ''
