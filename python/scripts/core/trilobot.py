from trilobot import *

singletonBot = None

def Trilobot():
    global singletonBot
    if singletonBot is None:
        singletonBot = trilobot.Trilobot()
    return singletonBot
