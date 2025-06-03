import netifaces

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
wifi_ip = get_wifi_ip()
print(f"Wi-Fi IP: {wifi_ip}")
