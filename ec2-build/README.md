EC2 Build Scripts
=================

This folder contains a collection of scripts and playbooks for building an Amazon EC2 instance running Training Wheels. We use this to continuously rebuild the master branch using Jenkins. These scripts are provided for anyone who would like to run a continuous build process, or adapt the build for their own purposes.

To run the `rebuild-server` script, you need Ansible and Euca2ools (Eucalyptus Tools) installed, as well as an Amazon EC2 account. Euca2ools is used to communicate with EC2, and Ansible used to run the playbooks. The steps below explain how to set these up.

We use a 'control' server within EC2 to tear up/down other servers. The control server can just be a micro instance, as all it needs to do is execute euca2ools and Ansible playbooks against the build servers.

There are some parts that are hardcoded for tw-build.com, pull requests welcome!

General
-------

rebuild-server requires the `timeout` command be available, easily installed on Ubuntu using:

    aptitude install timeout

Ansible
-------

On Ubuntu 12.04:

    aptitude -y install build-essential git python-dev python-jinja2 python-yaml python-paramiko python-software-properties python-pip

Then:

    sudo pip install pymongo
    add-apt-repository -y ppa:rquillo/ansible/ubuntu
    aptitude update
    aptitude install ansible

Euca2ools on Ubuntu
-------------------

(Change 'precise' to 'lucid' for 10.04 instead of 12.04.1)

    wget http://www.eucalyptus.com/sites/all/files/c1240596-eucalyptus-release-key.pub
    apt-key add c1240596-eucalyptus-release-key.pub
    echo "deb http://downloads.eucalyptus.com/software/euca2ools/2.1/ubuntu precise main" > /etc/apt/sources.list.d/euca2ools.list
    aptitude install euca2ools

Add EC2 credentials:

    mkdir /etc/euca2ools
    nano /etc/euca2ools/eucarc

    EC2_ACCESS_KEY="XXXX"
    EC2_SECRET_KEY="xxxxx"
    EC2_URL="http://ec2.amazonaws.com"

EC2 & Web Setup
---------------

1. Create an elastic IP address in EC2 and add the address to the `rebuild-server` script.
2. Purchase a domain name, for example training.com, and set the DNS A record to the elastic IP address.
3. Create an EC2 key pair and name it.
4. Put the key pair name into `rebuild-server` and install this key on your box so that you can SSH into all new instances. Use the following .ssh/config to allow you to connect to the instance, skipping host checking as that changes every time. Replace the host name and key name with the keys you have created:

    Host training.com
      StrictHostKeyChecking no
      UserKnownHostsFile=/dev/null
      IdentityFile ~/.ssh/tw.pem
      User ubuntu

5. Set your EC2 security group 'default' to allow incoming TCP traffic on ports 22, 8000 and 80.

Jenkins
-------

1. Under 'Source Code Management', enter the Training Wheels git repository details.
2. Under 'Build', execute Shell with the following:

    cd ec2-build
    ./rebuild-server
