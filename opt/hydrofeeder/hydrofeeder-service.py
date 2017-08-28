#!/usr/bin/env python

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

#Initiate Switch GPIO
with con:
    
    cur = con.cursor()    
    cur.execute("SELECT name, gpio, on_value FROM switch")

    while True:
        row = cur.fetchone()
        
        if row == None:
            break

        print 'Initiating Switch', row[0], row[1], row[2]

        if row[2] == 1:
           GPIO.setup(row[1], GPIO.IN, pull_up_down=GPIO.PUD_DOWN)
        else:
           GPIO.setup(row[1], GPIO.IN, pull_up_down=GPIO.PUD_UP)

while True:
    #Get Switch States
    print "Getting Switch States"
    with con:
        
        cur = con.cursor()    
        cur.execute("SELECT switch.name, switch.gpio, switch.on_value, switch_actions.name, switch_actions.function FROM switch INNER JOIN switch_actions ON switch.action_id = switch_actions.id;")
        while True:
            row = cur.fetchone()
            
            if row == None:
                break

            print 'Switch:', row[0], row[1], row[2], row[3], row[4]
            switch_gpio = row[1]
            switch_on_value = row[2]
            switch_function = row[4]

            switch_current_vaule = GPIO.input(switch_gpio)
            print 'gpio:', switch_gpio,' switch_current_vaule:', switch_current_vaule
            cur2 = con.cursor()
            cur2.execute('UPDATE switch SET current_value=?', (switch_current_vaule,))
            
            switch_active = 0
            if switch_on_value == 1:
                if switch_current_vaule == 1:
                    switch_active = 1
            else:
                if switch_current_vaule == 0:
                    switch_active = 1
                    
            if switch_active == 1:
                if switch_function == "pump_schedule":
                    print 'Running Pump Schedule'
                    #Get Schedule
                    with con:
                        
                        cur3 = con.cursor()    
                        cur3.execute("SELECT id, schedule_id, pump_id, add_ml FROM schedule")
                        while True:
                            row = cur3.fetchone()
                            
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

    #Get Manual Pump Jobs
    print "Getting Manual Pump Jobs"
    with con:
        
        cur = con.cursor()    
        cur.execute("SELECT id, grow_id, pump_id, add_ml FROM grow_pump_manual WHERE completed != 1")
        while True:
            row = cur.fetchone()
            
            if row == None:
                break

            print 'Manual Pump Job:', row[0], row[1], row[2], row[3]
            job_id = row[0]
            pump_id = row[2]
            add_ml = row[3]

            cur2 = con.cursor()
            cur2.execute('UPDATE grow_pump_manual SET in_progress = 1')
            
            cur3 = con.cursor()
            cur3.execute('SELECT id, name, gpio, ml_per_minute, on_value, off_value FROM pump WHERE id=?', (pump_id,))
            pump_values = cur3.fetchone()
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
               
            cur4 = con.cursor()
            cur4.execute('UPDATE grow_pump_manual SET in_progress = 0, completed = 1')
            
            time.sleep(fill_seconds); 
            
            if off_value == 1:
               GPIO.output(pump_gpio, GPIO.HIGH)
            else:
               GPIO.output(pump_gpio, GPIO.LOW)

            time.sleep(1)
    time.sleep(5)
GPIO.cleanup()
