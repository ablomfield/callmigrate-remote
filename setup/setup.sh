cd ~/callmigrate/setup
sudo mysql < callmigrate-remote.sql
mkdir /opt/callmigrate/logs/
sudo ln -s /opt/callmigrate/conf/100-callmigrate.conf /etc/apache2/sites-available/100-callmigrate.conf
sudo a2dissite 000-default
sudo a2ensite 100-callmigrate
sudo systemctl reload apache2
sudo cp callmigrate-tunnel.service /etc/systemd/system
sudo systemctl daemon-reload
sudo systemctl enable callmigrate-tunnel.service
sudo cp cm-cron /etc/cron.d
sudo cp cm-logrotate /etc/cron.d
sudo service cron restart
