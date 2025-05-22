import pyaudio
import numpy as np

def generate_sine_wave(frequency, duration, sample_rate=44100):
    t = np.linspace(0, duration, int(sample_rate*duration), False)
    note = np.sin(frequency * 2 * np.pi * t)
    audio = note * (2**15 - 1) // np.max(np.abs(note))
    audio = audio.astype(np.int16).tobytes()
    return audio

def play_audio(audio, sample_rate=44100):
    p = pyaudio.PyAudio()
    stream = p.open(format=pyaudio.paInt16,
                    channels=1,
                    rate=sample_rate,
                    output=True)
    stream.write(audio)
    stream.stop_stream()
    stream.close()
    p.terminate()

if __name__ == '__main__':
    frequency = 440  # Hz (A4 note)
    duration = 1  # seconds
    audio = generate_sine_wave(frequency, duration)
    play_audio(audio)
