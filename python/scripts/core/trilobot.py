import sys
import os

# Remove current directory from Python path temporarily
current_dir = os.path.dirname(os.path.abspath(__file__))
original_path = sys.path[:]
sys.path = [p for p in sys.path if os.path.abspath(p) != current_dir]

try:
    # Import the original trilobot module
    import trilobot as _original_trilobot
    
    # Import everything from original module into current namespace
    for attr_name in dir(_original_trilobot):
        if not attr_name.startswith('_'):
            globals()[attr_name] = getattr(_original_trilobot, attr_name)
    
    # Keep reference to original Trilobot class
    _OriginalTrilobot = _original_trilobot.Trilobot
    
finally:
    # Restore original Python path
    sys.path = original_path

singletonBot = None

def Trilobot():
    global singletonBot
    if singletonBot is None:
        singletonBot = _OriginalTrilobot.Trilobot()
    return singletonBot
