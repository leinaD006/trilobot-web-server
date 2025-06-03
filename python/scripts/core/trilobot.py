from trilobot import *

singletonBot = None

def Trilobot():
    global singletonBot
    if singletonBot is None:
        singletonBot = Trilobot()
    return singletonBot
