from trilobot import *
from trilobot import Trilobot as original_trilobot


singletonBot = None

def Trilobot():
    global singletonBot
    if singletonBot is None:
        singletonBot = original_trilobot.Trilobot()
    return singletonBot
