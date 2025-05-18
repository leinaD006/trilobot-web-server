#!/bin/bash

# MARIA_DB_ROOT_PASSWORD="Password"

# Set up the LAMP stack on a Raspberry Pi
sudo apt update

# Install packages
sudo apt install -y apache2 php libapache2-mod-php mariadb-server php-mysql


# Set up Apache
# sudo ufw allow in "Apache Full"
# sudo chmod -R 0755 /var/www/html/
sudo systemctl enable apache2 # Start Apache on boot
sudo systemctl start apache2

# Set up MariaDB
# sudo mysql -e "CREATE USER 'root' IDENTIFIED BY 'password';"
# sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH caching_sha2_password BY '$MARIA_DB_ROOT_PASSWORD';"

# sudo echo 'Include /etc/phpmyadmin/apache.conf' >> /etc/apache2/apache2.conf

sudo echo "<?php phpinfo(); ?>" > /var/www/html/info.php

sudo systemctl restart apache2

