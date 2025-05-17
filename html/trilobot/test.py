import sys
string = ''
for word in sys.argv[1:]:
	string += word + ' '

print(string)
print("Hello from Python!")
