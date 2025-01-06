# callmigrate-remote
Remote Agent for CallMigrate.click utility

To install:

echo '' | sudo EDITOR='tee -a' visudo
echo '# Allow cmadmin without password' | sudo EDITOR='tee -a' visudo
echo 'cmadmin ALL=(ALL) NOPASSWD: ALL' | sudo EDITOR='tee -a' visudo

sudo mkdir /opt/callmigrate
sudo chown -R cmadmin:cmadmin /opt/callmigrate/
ln -s /opt/callmigrate/ ~/callmigrate
cd ~/callmigrate
git clone https://github.com/ablomfield/callmigrate-remote.git .

cd setup
chmod +x *.sh
./install.sh
./setup.sh
