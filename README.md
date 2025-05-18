# Trilobot Web Server

## Installation

1. Clone the repository:
    ```bash
    git clone https://github.com/leinaD006/trilobot-web-server.git
    ```
2. Change to the project directory:
    ```bash
    cd trilobot-web-server
    ```
3. Run the install script:
    ```bash
    ./install.sh
    ```
4. Enter sudo password whenever prompted.

5. When the PHPMyAdmin config screen appears, hit space to select "apache2" (asterisk will appear), them hit tab (or use arrow keys) to select "OK" and hit enter. On the next screen, hit enter to select "yes". When it asks for a password, leave it blank and hit enter. The script will set the password automatically.

## Activate the virtual environment

Use the bash script

```bash
./activate.sh
```

or manually activate it:

```bash
source env/bin/activate
```
