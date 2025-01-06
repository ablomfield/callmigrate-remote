sudo touch /etc/ssh/ssh_known_hosts
sudo ssh-keyscan -H callmigrate.click >> /etc/ssh/ssh_known_hosts
sudo apt-get update
sudo apt-get upgrade
sudo apt-get install php php-yaml mysql-server apache2 autossh
