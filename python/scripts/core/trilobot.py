import sys
import os

# Temporarily remove current directory from path to import original trilobot
current_dir = os.path.dirname(os.path.abspath(__file__))
if current_dir in sys.path:
    sys.path.remove(current_dir)

# Import everything from the original trilobot module
from trilobot import *
from trilobot import Trilobot as _OriginalTrilobot

# Restore the path
sys.path.insert(0, current_dir)

singletonBot = None

def Trilobot():
    global singletonBot
    if singletonBot is None:
        singletonBot = _OriginalTrilobot.Trilobot()
    return singletonBot
