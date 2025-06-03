import socket

def get_ip_address():
    try:
        # This doesn't need to be a real reachable address; just used to find the right interface
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect(("8.8.8.8", 80))  # Google's public DNS
        ip = s.getsockname()[0]
        s.close()
        return ip
    except Exception as e:
        return f"Error getting IP address: {e}"

# Usage
ip_address = get_ip_address()
print(f"My IP address is: {ip_address}")
