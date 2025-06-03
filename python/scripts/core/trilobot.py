import sys
import os

# Temporarily remove current directory from path to import original trilobot
current_dir = os.path.dirname(os.path.abspath(__file__))
print(f"Current directory: {current_dir}")
if current_dir in sys.path:
    sys.path.remove(current_dir)

# Import everything from the original trilobot module
from trilobot import Trilobot as _OriginalTrilobot

# Restore the path
sys.path.insert(0, current_dir)

BUTTON_A = 0
BUTTON_B = 1
BUTTON_X = 2
BUTTON_Y = 3
NUM_BUTTONS = 4

# Underlighting LED locations
LIGHT_FRONT_RIGHT = 0
LIGHT_FRONT_LEFT = 1
LIGHT_MIDDLE_LEFT = 2
LIGHT_REAR_LEFT = 3
LIGHT_REAR_RIGHT = 4
LIGHT_MIDDLE_RIGHT = 5
NUM_UNDERLIGHTS = 6

# Useful underlighting groups
LIGHTS_LEFT = (LIGHT_FRONT_LEFT, LIGHT_MIDDLE_LEFT, LIGHT_REAR_LEFT)
LIGHTS_RIGHT = (LIGHT_FRONT_RIGHT, LIGHT_MIDDLE_RIGHT, LIGHT_REAR_RIGHT)
LIGHTS_FRONT = (LIGHT_FRONT_LEFT, LIGHT_FRONT_RIGHT)
LIGHTS_MIDDLE = (LIGHT_MIDDLE_LEFT, LIGHT_MIDDLE_RIGHT)
LIGHTS_REAR = (LIGHT_REAR_LEFT, LIGHT_REAR_RIGHT)
LIGHTS_LEFT_DIAGONAL = (LIGHT_FRONT_LEFT, LIGHT_REAR_RIGHT)
LIGHTS_RIGHT_DIAGONAL = (LIGHT_FRONT_RIGHT, LIGHT_REAR_LEFT)

# Motor names
MOTOR_LEFT = 0
MOTOR_RIGHT = 1
NUM_MOTORS = 2

singletonBot = None

def Trilobot():
    global singletonBot
    if singletonBot is None:
        singletonBot = _OriginalTrilobot.Trilobot()
    return singletonBot
