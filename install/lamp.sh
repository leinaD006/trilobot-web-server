#!/bin/bash
set -e

MARIA_DB_ROOT_PASSWORD="Password"

# Set up the LAMP stack on a Raspberry Pi
sudo apt update

# Install packages
sudo apt install -y apache2 php libapache2-mod-php mariadb-server php-mysql

# Set up Apache
sudo systemctl enable apache2 # Start Apache on boot
sudo systemctl start apache2

# Set up PHPMyAdmin
sudo apt install phpmyadmin -y
sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$MARIA_DB_ROOT_PASSWORD';"

# Remove default index.html
if [ -f /var/www/html/index.html ]; then
    sudo rm /var/www/html/index.html
else
    sleep 5
    if [ -f /var/www/html/index.html ]; then
        sudo rm /var/www/html/index.html
    fi
fi


# Give apache user permission to gpio and i2c
sudo usermod -a -G gpio www-data
sudo usermod -a -G i2c www-data

# Final restart
sudo systemctl restart apache2

