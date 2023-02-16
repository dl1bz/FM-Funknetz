#!/usr/bin/env python3

import serial

# serport = '/dev/ttyAMA0'
serport = '/dev/ttyUSB0'

baud = '9600'

channelspace = '1'      # 0=12.5kHz, 1=25kHz

rxfreq = '430.0250'     # TX frequency
txfreq = rxfreq         # Same as rx freq - we work simplex

squelch = '2'           # 0-8 (0 = open)

txcxcss = '0004'       # CTCSS 77Hz
rxcxcss = '0004'        # CTCSS 77Hz
# txcxcss = rxcxcss

# txcxcss = '023N'        # CTCSS / CDCSS TX
# rxcxcss = '023N'        # CTCSS / CDCSS RX

flataudio = '1'           # switch to discriminator output and input if value = 1
bypass_lowpass = '1'      # bypass lowpass-filter if value = 1
bypass_highpass = '1'     # bypass highpass-filter if value = 1

volume = '7'              # betweeen 0..8

ser = serial.Serial(serport, baud, timeout=2)
print('Opening port: ' + ser.name) 

print ('\r\nConnecting...')
ser.write(b'AT+DMOCONNECT\r\n')

output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

print ('\r\nConfiguring radio...')
config = 'AT+DMOSETGROUP={},{},{},{},{},{}\r\n'.format(channelspace, txfreq, rxfreq, txcxcss, squelch, rxcxcss)
print (config)
ser.write(config.encode())
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

print ('\r\nSet filter...')
config = 'AT+SETFILTER={},{},{}\r\n'.format(flataudio, bypass_highpass, bypass_lowpass)
print(config)
ser.write(config.encode())
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

print ('\r\nSetting volume...')
config = 'AT+DMOSETVOLUME={}\r\n'.format(volume)
print(config)
ser.write(config.encode())
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

print ('\r\nSetting emission tail tone...')
ser.write(b'AT+SETTAIL=0\r\n')
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

print ('\r\nGetting Module Version...')
ser.write(b'AT+VERSION\r\n')
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

print ('\r\nGetting Settings...')
ser.write(b'AT+DMOREADGROUP\r\n')
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))
