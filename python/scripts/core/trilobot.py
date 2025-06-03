from trilobot import *

tbot = None

def Trilobot():
    global tbot
    if tbot is None:
        tbot = Trilobot()
    return tbot
