#!/usr/bin/python
# -*- coding: utf-8 -*-

import sqlite3 as lite
import RPi.GPIO as GPIO
import time
import sys

GPIO.setmode(GPIO.BCM)

con = lite.connect('/opt/hydrofeeder/hydrofeeder.sqlite')

#Initiate PUMP GPIO
with con:
    
    cur = con.cursor()    
    cur.execute("SELECT id, name, gpio, ml_per_minute, on_value, off_value FROM pump")

    while True:
        row = cur.fetchone()
        
        if row == None:
            break

        print 'Initiating Pump', row[0], row[1], row[2], row[3], row[4], row[5]

        GPIO.setup(row[2], GPIO.OUT)
        if row[5] == 1:
           GPIO.output(row[2], GPIO.HIGH)
        else:
           GPIO.output(row[2], GPIO.LOW)

#Get Schedule
with con:
    
    cur = con.cursor()    
    cur.execute("SELECT id, schedule_id, pump_id, add_ml FROM schedule")
    while True:
        row = cur.fetchone()
        
        if row == None:
            break

        print 'Schedule', row[0], row[1], row[2], row[3]
        pump_id = row[2]
        add_ml = row[3]
        
        cur2 = con.cursor()
        cur2.execute('SELECT id, name, gpio, ml_per_minute, on_value, off_value FROM pump WHERE id=?', (pump_id,))
        pump_values = cur2.fetchone()
        pump_gpio = pump_values[2]
        pump_name = pump_values[1]
        ml_per_minute = pump_values[3]
        on_value = pump_values[4]
        off_value = pump_values[5]
        
        fill_seconds = (float(add_ml) / float(ml_per_minute)) * 60
        print fill_seconds, add_ml, ml_per_minute, (float(add_ml) / float(ml_per_minute))
        
        print 'Pumping', pump_name, 'for', fill_seconds, 'seconds'
        if on_value == 1:
           GPIO.output(pump_gpio, GPIO.HIGH)
        else:
           GPIO.output(pump_gpio, GPIO.LOW)
        
        time.sleep(fill_seconds); 
        
        if off_value == 1:
           GPIO.output(pump_gpio, GPIO.HIGH)
        else:
           GPIO.output(pump_gpio, GPIO.LOW)

        time.sleep(1)

GPIO.cleanup()
