#!/usr/bin/env python3

import serial
import time

serport = '/dev/ttyUSB0'

baud = '9600'

channelspace = '1'      # 0=12.5kHz, 1=25kHz

# Frequenz bitte nach Belieben anpassen, kHz bitte immer 4stellig angeben - Bandplan beachten !
rxfreq = '430.0250'     # TX frequency
txfreq = rxfreq         # Same as rx freq - we work simplex

# sinnvolle Werte liegen zwischen 2 und 4, je kleiner desto empfindlichere SQL, 0 bedeutet SQL immer offen
squelch = '0'           # 0-8 (0 = open)

# CTCSS Konfiguration
txcxcss = '0'        # CTCSS TX OFF
rxcxcss = '0'        # CTCSS RX OFF
# txcxcss = rxcxcss

# DCS anstatt CTCSS - nicht empfohlen
# txcxcss = '023N'        # CTCSS / CDCSS TX
# rxcxcss = '023N'        # CTCSS / CDCSS RX

# ein Wert von 7 ist weitgehend optimal als Sound-Input
volume = '7'              # betweeen 0..8

# ab hier am besten nichts editieren - es sei denn, man weiss was man tut und warum :)

ser = serial.Serial(serport, baud, timeout=2)
print('Opening port: ' + ser.name)

print ('\r\nConnecting...')
ser.write(b'AT+DMOCONNECT\r\n')
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

time.sleep(3)

# print ('\r\nRESET...')
# ser.write(b'AT+DMOREST\r\n')
# output = ser.readline()
# print ('reply: ' + output.decode("utf-8"))

# time.sleep(5)

# print ('\r\nConnecting...')
# ser.write(b'AT+DMOCONNECT\r\n')
# output = ser.readline()
# print ('reply: ' + output.decode("utf-8"))

# time.sleep(3)

print ('\r\nConfiguring radio settings...')
config = 'AT+DMOSETGROUP={},{},{},{},{},{},4\r\n'.format(channelspace, rxfreq, txfreq, rxcxcss, squelch, txcxcss)
print (config)
ser.write(config.encode())
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

time.sleep(3)

# Programmierung und Parametrisierung ist dem Datenblatt des SR110U zu entnehmen

print ('\r\nPower SAVE OFF...')
ser.write(b'AT+DMOAUTOPOWCONTR=1\r\n')
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

time.sleep(3)

print ('\r\nVOX OFF...')
ser.write(b'AT+DMOSETVOX=0\r\n')
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

time.sleep(3)

print ('\r\nMic setting level 6...')
config = 'AT+DMOFUN={},6,0,0,0\r\n'.format(squelch)
print(config)
ser.write(config.encode())
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

time.sleep(3)

print ('\r\nSetting volume...')
config = 'AT+DMOSETVOLUME={}\r\n'.format(volume)
print(config)
ser.write(config.encode())
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

time.sleep(3)

print ('\r\nGetting Module Version...')
ser.write(b'AT+DMOVERQ\r\n')
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

print ('\r\nProgrammierung abgeschlossen...')
