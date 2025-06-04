import os
import time
from trilobot import *

class ScriptLoader:
    def __init__(self, config_file):
        self.config_file = config_file
        self.config = {}
        self.tbot = Trilobot()

    def getTbot(self):
        return self.tbot

    def load_config(self):
        if os.path.exists(self.config_file):
            with open(self.config_file, 'r') as f:
                for line in f:
                    line = line.strip()
                    if '=' in line:
                        key, value = line.split('=', 1)
                        key = key.strip()
                        value = value.strip()
                        if key and value:
                            self.config[key] = value
                        else:
                            print(f"Invalid line in config: {line}")
                    else:
                        print(f"Invalid line in config: {line}")

        else:
            print(f"Configuration file {self.config_file} does not exist. Please check the path.")
            exit(1)
                        
    def run_script(self, script_path):
        full_path = os.path.join(self.config['BASE_DIR'], script_path)
        if os.path.exists(full_path):
            try:
                with open(full_path, 'r') as script_file:
                    script_content = script_file.read()
                    
                    # Create execution context with shared tbot instance
                    exec_globals = {
                        '__name__': '__main__',
                        '__builtins__': __builtins__,
                        'tbot': self.tbot,  # Inject our tbot instance
                        'Trilobot': lambda: self.tbot,  # Override Trilobot constructor
                        'time': time,
                        'RED': (255, 0, 0),
                        'GREEN': (0, 255, 0),
                        'BLUE': (0, 0, 255),
                    }
                    
                    # Import trilobot module contents into the execution context
                    from trilobot import *
                    exec_globals.update({k: v for k, v in globals().items() if not k.startswith('_')})
                    
                    exec(script_content, exec_globals)
                    
            except Exception as e:
                print(f"Error executing script {full_path}: {e}")
        else:
            print(f"Script file {full_path} does not exist. Please check the configuration.")

    def flash_buttons(self, num_flashes=1):
        for _ in range(num_flashes):
            self.tbot.set_button_led(BUTTON_A, 1)
            time.sleep(0.1)
            self.tbot.set_button_led(BUTTON_A, 0)
            self.tbot.set_button_led(BUTTON_B, 1)
            time.sleep(0.1)
            self.tbot.set_button_led(BUTTON_B, 0)
            self.tbot.set_button_led(BUTTON_X, 1)
            time.sleep(0.1)
            self.tbot.set_button_led(BUTTON_X, 0)
            self.tbot.set_button_led(BUTTON_Y, 1)
            time.sleep(0.1)
            self.tbot.set_button_led(BUTTON_Y, 0)
    


if __name__ == "__main__":
    config_file = '/var/www/python/buttons.conf'
    loader = ScriptLoader(config_file)
    loader.load_config()
    loader.flash_buttons(2)

    while True:
        if loader.tbot.read_button(BUTTON_A):
            loader.run_script(loader.config['BUTTON_A'])
            
        if loader.tbot.read_button(BUTTON_B):
            loader.run_script(loader.config['BUTTON_B'])

        if loader.tbot.read_button(BUTTON_X):
            loader.run_script(loader.config['BUTTON_X'])

        if loader.tbot.read_button(BUTTON_Y):
            loader.run_script(loader.config['BUTTON_Y'])
            



        