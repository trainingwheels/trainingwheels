Setup Vagrant Dev Environment
-----------------------------

1. Download and install [Vagrant](http://www.vagrantup.com/) and [VirtualBox](https://www.virtualbox.org/) as per the Vagrant "Getting started" page.
2. Run `vagrant up`
3. Add to your /etc/hosts, to get started:

    10.1.0.2 training.wheels instructor.mycourse.training.wheels bobby.mycourse.training.wheels sally.mycourse.training.wheels

4. Run `vagrant ssh` to connect.
5. Visit the controller at http://training.wheels:8000/, the username and password are `tw` / `admin`, unless you changed them in `provision/controller-settings-vagrant.yml`
6. See sample user at http://instructor.mycourse.training.wheels/
7. Visit the user's Cloud9 IDE at http://instructor.mycourse.training.wheels:31001/

What is all this magic?
-----------------------

* A new VirtualBox virtual machine is started up in 'headless' mode, using the standard Ubuntu 12.04 base box. This base box is downloaded and stored in a shared location for use by other Vagrant profiles.
* Ansible is then installed on the box, and the setup playbooks for both the controller and server provisioning are run, followed by the developer setup playbook.
* Vagrant is forwarding the ports from localhost to the virtual machine. This port mapping is defined in the file called `VagrantFile`.
* Vagrant mounts the current clone of the Github repository on your host, at `/var/trainingwheels` in the virtual machine. This is done using NFS, rather than the VirtualBox shared folders, which are documented as being far, far slower. You can develop on your host in your clone, and have the files served instantly and transparently through the VM. The mount point is defined in /etc/exports on your host.
* You can type `vagrant reload` to reload a changed VagrantFile config, or re-run the playbooks if they've been updated.

Commands
--------

* Try `vagrant destroy` then `vagrant up` to completely rebuild the VM.

Classroom
---------

Training Wheels can manage any number of remote servers, and one sample configuration is provided by default (course 2). This second classroom server must be provisioned after the controller, as it picks up the Training Wheels public key automatically so that the controller can connect to it. If you re-provision the controller, you'll need to re-provision the classroom, too, so that the new key is picked up. To start up the second server:

1. `cd vagrant/classroom`
2. `vagrant up`
3. Add to your /etc/hosts:

    10.1.0.3 class.training.wheels instructor.sshcourse.class.training.wheels jenny.sshcourse.class.training.wheels harry.sshcourse.class.training.wheels

Additional provisioning
-----------------------

If you want special provisioning steps taken, you can place a setup.local.sh bash script in this directory and it will be run after the other provisioning steps.

If you want to run a custom Ansible playbook from this script, name it (or them) *.local.yml, which is ignored in this repo's .gitignore.

Vagrant recovery
----------------

If you accidentally delete your .vagrant file or this directory, use the VirtualBox commands to recover:

    VBoxManage list runningvms
    VBoxManage list vms

Make a new .vagrant if necessary. Looks like:

    {"active":{"trainingwheels":"ec426377-dbe6-4be7-4751-766956e44958"}}

Why not use the Vagrant Ansible plugin?
---------------------------------------

The problem is the setup of Ansible on the host Mac OSX machine. It's not straightforward as it requires Python modules be built. It's far simpler on Linux, but we must support OSX. Secondly, we need Ansible installed on the guest machine running the controller, so we may as well do it as the first step.

Shared folders
--------------

Currently we use NFS, be sure to check /etc/exports, make sure you don't export a parent of this directory, as that prevents this one from being exported.

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

If you have this plugin installed, it will update guest additions automatically. Just make sure that you always compile them when your kernel changes. If you're doing a lot of reloading of your VM, then you probably don't want this plugin installed, as it takes a long time to rebuild the guest additions.
