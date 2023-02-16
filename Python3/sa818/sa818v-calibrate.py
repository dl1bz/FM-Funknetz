#!/usr/bin/python3

import serial

# serport = '/dev/ttyAMA0'
serport = '/dev/ttyUSB0'
baud = '9600'

channelspace = '1'      # 0=12.5kHz, 1=25kHz

rxfreq = '144.8250'     # TX frequency
txfreq = rxfreq         # Same as rx freq.

squelch = '0'           # 0-8 (0 = open)

txcxcss = '0000'        # CTCSS / CDCSS TX
rxcxcss = '0000'        # CTCSS / CDCSS RX

# txcxcss = '023N'        # CTCSS / CDCSS TX
# rxcxcss = '023N'        # CTCSS / CDCSS RX


ser = serial.Serial(serport, baud, timeout=2)
print('Opening port: ' + ser.name) 

print ('\r\nConnecting...')
ser.write(b'AT+DMOCONNECT\r\n')

output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

print ('\r\nConfiguring radio...')
config = 'AT+DMOSETGROUP={},{},{},{},{},{}\r\n'.format(channelspace, txfreq, rxfreq, txcxcss, squelch, rxcxcss)
ser.write(config.encode())
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

print ('\r\nDisabling filters...')
ser.write(b'AT+SETFILTER=1,1,1\r\n')
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

print ('\r\nSetting volume...')
# F8ASB VOL 2-3
ser.write(b'AT+DMOSETVOLUME=7\r\n')
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))

print ('\r\nSetting emission tail tone...')
ser.write(b'AT+SETTAIL=0\r\n')
output = ser.readline()
print ('reply: ' + output.decode("utf-8"))
