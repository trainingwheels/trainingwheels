Setup Vagrant Dev Environment
-----------------------------

1. Download and install Vagrant and VirtualBox as per the Vagrant "Getting started" page.
2. Run `vagrant up`

Why not use the Vagrant Ansible plugin?
---------------------------------------

The problem is the setup of Ansible on the host Mac OSX machine. It's not straightforward as it requires Python modules be built. It's far simpler on Linux, but we must support OSX.
