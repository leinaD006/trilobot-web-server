import netifaces
from time import sleep
from trilobot import *

light_groups = {
    "1" : (LIGHT_MIDDLE_LEFT,),
    "2" : (LIGHT_FRONT_LEFT,),
    "3" : (LIGHT_MIDDLE_LEFT, LIGHT_FRONT_LEFT),
    "4" : (LIGHT_FRONT_RIGHT,),
    "5" : (LIGHT_FRONT_RIGHT, LIGHT_MIDDLE_LEFT),
    "6" : (LIGHT_FRONT_RIGHT, LIGHT_FRONT_LEFT),
    "7" : (LIGHT_FRONT_RIGHT, LIGHT_FRONT_LEFT, LIGHT_MIDDLE_LEFT),
    "8" : (LIGHT_MIDDLE_RIGHT,),
    "9" : (LIGHT_MIDDLE_RIGHT, LIGHT_MIDDLE_LEFT),
    "0" : (LIGHT_MIDDLE_RIGHT, LIGHT_FRONT_RIGHT, LIGHT_FRONT_LEFT, LIGHT_MIDDLE_LEFT),
    "." : (LIGHT_REAR_RIGHT,),
    "a" : (LIGHT_REAR_RIGHT, LIGHT_MIDDLE_RIGHT, LIGHT_FRONT_RIGHT, LIGHT_FRONT_LEFT, LIGHT_MIDDLE_LEFT, LIGHT_REAR_LEFT),
}

RED = (50, 0, 0)
GREEN = (0, 50, 0)
BLUE = (0, 0, 50)


def get_wifi_ip(interface='wlan0'):
    try:
        addresses = netifaces.ifaddresses(interface)
        ip_info = addresses.get(netifaces.AF_INET)
        if ip_info and len(ip_info) > 0:
            return ip_info[0]['addr']
        else:
            return "No IP address found for interface"
    except ValueError:
        return f"Interface '{interface}' not found"
    
sleep(10) # Wait to ensure network interfaces are up
wifi_ip = get_wifi_ip("wlan0")

if wifi_ip == "No IP address found for interface":
    wifi_ip = get_wifi_ip("eth0")

tbot = Trilobot()

print(f"WiFi IP Address: {wifi_ip}")

if wifi_ip == "No IP address found for interface":
    print("Connect to WiFi to get an IP address.")
    for i in range(3):
        if tbot.read_button(BUTTON_X):
            print("Button X pressed, exiting...")
            tbot.clear_underlights(light_groups["a"])
            exit(0)
        tbot.set_underlights(light_groups["a"], RED)
        sleep(0.5)
        tbot.clear_underlights(light_groups["a"])
        sleep(0.5)
    print("Exiting script.")
    exit(1)

while True:
    tbot.set_underlights(light_groups["a"], GREEN)
    sleep(0.5)
    tbot.clear_underlights(light_groups["a"])
    sleep(0.5)

    for c in wifi_ip:
        if tbot.read_button(BUTTON_X):
            print("Button X pressed, exiting...")
            tbot.clear_underlights(light_groups["a"])
            exit(0)

        tbot.set_underlights(light_groups[c], BLUE)
        sleep(2)
        tbot.clear_underlights(light_groups[c])
        sleep(0.5)
