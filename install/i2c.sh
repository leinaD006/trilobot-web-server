#!/bin/bash

# Enable I2C on Raspberry Pi
echo "Enabling I2C on Raspberry Pi..."
sudo raspi-config nonint do_i2c 0
echo "I2C enabled successfully."