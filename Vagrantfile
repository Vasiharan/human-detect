# -*- mode: ruby -*-
# vi: set ft=ruby :
Vagrant.configure("2") do |config|
    config.vm.box = "brodie-slater/centos-7-dev"
    config.vm.network "private_network", ip: "192.168.45.22"
    config.vm.hostname = "scotchbox"
    # Optional NFS. Make sure to remove other synced_folder line too
    config.vm.synced_folder ".", "/var/www", :nfs => { :mount_options => ["dmode=777","fmode=666"] }
end
