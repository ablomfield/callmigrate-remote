echo '# Allow cmadmin without password' | sudo EDITOR='tee -a' visudo
echo 'cmadmin ALL=(ALL) NOPASSWD: ALL' | sudo EDITOR='tee -a' visudo
sudo ssh-keyscan -H callmigrate.click >> /etc/ssh/ssh_known_hosts
sudo mkdir /opt/callmigrate
sudo chown -R cmadmin:cmadmin /opt/callmigrate/
ln -s /opt/callmigrate/ ~/callmigrate
cd ~/callmigrate
git clone https://github.com/ablomfield/callmigrate-remote.git .
cd ~/callmigrate/setup
sudo mysql < callmigrate-remote.sql
mkdir /opt/callmigrate/logs/
sudo cp 100-callmigrate.conf /etc/apache2/sites-available
sudo a2dissite 000-default
sudo a2ensite 100-callmigrate
sudo systemctl reload apache2
sudo cp callmigrate-tunnel.service /etc/systemd/system
sudo systemctl daemon-reload
sudo systemctl enable callmigrate-tunnel.service
sudo cp cm-cron /etc/cron.d
sudo cp cm-logrotate /etc/cron.d