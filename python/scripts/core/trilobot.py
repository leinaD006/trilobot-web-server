from trilobot import *
import builtins

# Use builtins.__import__ to get the real module before name collision
_original_import = builtins.__import__
original_trilobot = _original_import('trilobot')

singletonBot = None

def Trilobot():
    global singletonBot
    if singletonBot is None:
        singletonBot = original_trilobot.Trilobot()
    return singletonBot
