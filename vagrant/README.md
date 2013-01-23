Setup Vagrant Dev Environment
-----------------------------

1. Download and install Vagrant and VirtualBox as per the Vagrant "Getting started" page.
2. Run `vagrant up`
3. Add to your /etc/hosts:

    # Training Wheels
    127.0.0.1  training.wheels instructor.mycourse.training.wheels bobby.mycourse.training.wheels sally.mycourse.training.wheels

A new Vagrant virtual machine is started up, using the standard Ubuntu 12.04 base box. Ansible is installed on the box, and the setup playbooks for both the controller and classroom are run.

Vagrant commands
----------------

`vagrant reload` to reload a changed VagrantFile config

If you accidentally delete your .vagrant file or this directory, use the VirtualBox commands to recover:

    VBoxManage list runningvms
    VBoxManage list vms

Make a new .vagrant if necessary. Looks like:

    {"active":{"trainingwheels":"ec426377-dbe6-4be7-4751-766956e44958"}}

Why not use the Vagrant Ansible plugin?
---------------------------------------

The problem is the setup of Ansible on the host Mac OSX machine. It's not straightforward as it requires Python modules be built. It's far simpler on Linux, but we must support OSX.

Shared folders
--------------

Currently we use NFS, be sure to check /etc/exports, make sure you don't export a parent of this directory, as that prevents this one from beign exported.

Package updates (optional)
--------------------------

You can update to the latest packages, if you like, by doing the following:

1. `vagrant ssh`
2. `sudo aptitude update`
2. `sudo aptitude upgrade`

If asked about installing GRUB on a hard disk, choose the one labeled VBOX_HARDDISK.

Guest Additions (optional)
--------------------------

Guest additions can be upgraded by doing the following (make sure you reboot if you've just updated the kernel in the steps above):

1. `vagrant gem install vagrant-vbguest` installs [vagrant-vbguest](https://github.com/dotless-de/vagrant-vbguest).
2. `vagrant vbguest --do install`

In usual operation, the vbguest plugin will automatically try upgrade the additions, this is not enabled in this VagrantFile because we don't want to compile the upgraded additions before updating the kernel.
