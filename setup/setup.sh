cd /opt/callmigrate/setup
sudo mysql < /opt/callmigrate/conf/callmigrate-remote.sql
sudo ln -s /opt/callmigrate/conf/100-callmigrate.conf /etc/apache2/sites-available/100-callmigrate.conf
sudo a2dissite 000-default
sudo a2ensite 100-callmigrate
sudo systemctl reload apache2
sudo cp /opt/callmigrate/conf/callmigrate-tunnel.service /etc/systemd/system
sudo systemctl daemon-reload
sudo systemctl enable callmigrate-tunnel.service
sudo cp /opt/callmigrate/conf/cm-cron /etc/cron.d
sudo cp /opt/callmigrate/conf/cm-logrotate /etc/cron.d
sudo service cron restart
