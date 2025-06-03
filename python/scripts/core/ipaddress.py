import netifaces
from trilobot import *
light_groups = {
    "1" : (LIGHT_MIDDLE_LEFT),
    "2" : (LIGHT_FRONT_LEFT),
    "3" : (LIGHT_MIDDLE_LEFT, LIGHT_FRONT_LEFT),
    "4" : (LIGHT_FRONT_RIGHT),
    "5" : (LIGHT_FRONT_RIGHT, LIGHT_MIDDLE_LEFT),
    "6" : (LIGHT_FRONT_RIGHT, LIGHT_FRONT_LEFT),
    "7" : (LIGHT_FRONT_RIGHT, LIGHT_FRONT_LEFT, LIGHT_MIDDLE_LEFT),
    "8" : (LIGHT_MIDDLE_RIGHT),
    "9" : (LIGHT_MIDDLE_RIGHT, LIGHT_MIDDLE_LEFT),
    "0" : (LIGHT_MIDDLE_RIGHT, LIGHT_FRONT_RIGHT, LIGHT_FRONT_LEFT, LIGHT_MIDDLE_LEFT),
    "." : (LIGHT_REAR_RIGHT),
}

RED = (255, 0, 0)
GREEN = (0, 255, 0)
BLUE = (0, 0, 255)


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
    
# Usage
wifi_ip = get_wifi_ip("eth0")

tbot = Trilobot()

print(f"WiFi IP Address: {wifi_ip}")

for c in wifi_ip:
    tbot.set_underlights(light_groups[c], RED)
    sleep(1)
    tbot.clear_underlights(light_groups[c])
    sleep(0.2)
