#!/usr/bin/env python

import sqlite3 as lite
import RPi.GPIO as GPIO
import time
import datetime
from pytz import timezone
import pytz
import sys
import smtplib
from email.MIMEMultipart import MIMEMultipart
from email.MIMEText import MIMEText

GPIO.setmode(GPIO.BCM)
con = lite.connect('/opt/hydrofeeder/hydrofeeder.sqlite')

#Set Email Settings
mailserver = smtplib.SMTP('smtp.gmail.com', 587)
mailserver.starttls()
mailserver.login("user@email.com", "notthepassword")
mailfrom = "user@email.com"
mailto = "dest@email.com"
body = ""

datestamp = datetime.datetime.now() # some timestamp
old_timezone = pytz.utc
new_timezone = pytz.timezone("US/Eastern")
datestamp = str(old_timezone.localize(datestamp).astimezone(new_timezone).strftime("%A, %d. %B %Y %I:%M%p"))

def output_text(input):
    print input
    return input + "\r\n"
    
def distance():
    # set Trigger to HIGH
    GPIO.output(GPIO_TRIGGER, True)
 
    # set Trigger after 0.01ms to LOW
    time.sleep(0.00001)
    GPIO.output(GPIO_TRIGGER, False)
 
    StartTime = time.time()
    StopTime = time.time()
 
    # save StartTime
    while GPIO.input(GPIO_ECHO) == 0:
        StartTime = time.time()
 
    # save time of arrival
    while GPIO.input(GPIO_ECHO) == 1:
        StopTime = time.time()
 
    # time difference between start and arrival
    TimeElapsed = StopTime - StartTime
    # multiply with the sonic speed (34300 cm/s)
    # and divide by 2, because there and back
    distance = ((TimeElapsed * 34300) / 2) * 0.39370078740158
 
    return distance
    
#Initiate Ultrasonic Distance Sensor
body += output_text("--------Initiating Ultrasonic Distance Sensor--------")

GPIO_TRIGGER = 24
GPIO_ECHO = 23
GPIO.setup(GPIO_TRIGGER, GPIO.OUT)
GPIO.setup(GPIO_ECHO, GPIO.IN)
body += output_text("Trigger GPIO:" + str(GPIO_TRIGGER) + " Echo GPIO:" + str(GPIO_ECHO) + "")

#Initiate PUMP GPIO
body += output_text("\r\n--------Initiating PUMP GPIO--------")
with con:
    
    cur = con.cursor()    
    cur.execute("SELECT id, name, gpio, ml_per_minute, on_value, off_value FROM pump")

    while True:
        row = cur.fetchone()
        
        if row == None:
            break

        body += output_text("Initiating Pump - ID:" + str(row[0]) + " - GPIO:" + str(row[2]) + " - Name:" + str(row[1]) + " - ml_per_minute:" + str(row[3]) + " - On Value:" + str(row[4]) + " - Off Value:" + str(row[5]) + "")

        GPIO.setup(row[2], GPIO.OUT)
        if row[5] == 1:
           GPIO.output(row[2], GPIO.HIGH)
        else:
           GPIO.output(row[2], GPIO.LOW)

#Initiate Switch GPIO
body += output_text("\r\n--------Initiating Switch GPIO--------")
with con:
    
    cur = con.cursor()    
    cur.execute("SELECT id, name, gpio, on_value FROM switch")

    while True:
        row = cur.fetchone()
        
        if row == None:
            break

        body += output_text("Initiating Switch - ID:" + str(row[0]) + " - GPIO:" + str(row[2]) + " - Name:" + str(row[1]) + " - On Value:" + str(row[3]) + "")

        if row[3] == 1:
           GPIO.setup(row[2], GPIO.IN, pull_up_down=GPIO.PUD_DOWN)
        else:
           GPIO.setup(row[2], GPIO.IN, pull_up_down=GPIO.PUD_UP)
           
#Get Current Water Height
body += output_text("\r\n--------Current Water Height--------")
Max_Size =  14
L = []
for i in range(100):
    L.append(distance())
    time.sleep(.05)
dist = sum(L) / float(len(L))
Water_Height = Max_Size - dist

body += output_text("Measured Distance:" + str(dist) + "")
body += output_text("Water Height:" + str(Water_Height) + "")

#Get Switch States
body += output_text("\r\n--------Getting Switch States--------")
with con:
    
    cur = con.cursor()    
    cur.execute("SELECT switch.id, switch.name, switch.gpio, switch.on_value, switch_actions.name, switch_actions.function FROM switch INNER JOIN switch_actions ON switch.action_id = switch_actions.id;")
    while True:
        row = cur.fetchone()
        
        if row == None:
            break

        body += output_text("**** Switch - ID:" + str(row[0]) + " - GPIO:" + str(row[2]) + " - Name:" + str(row[1]) + " - On Value:" + str(row[3]) + " - Action Name:" + str(row[4]) + " - Function:" + str(row[5]) + " ****")
        
        switch_gpio = row[2]
        switch_on_value = row[3]
        switch_function = row[5]
        switch_current_vaule = GPIO.input(switch_gpio)
        
        cur2 = con.cursor()
        cur2.execute('UPDATE switch SET current_value=?', (switch_current_vaule,))        
        
        body += output_text("switch_current_vaule: " + str(switch_current_vaule) + "")
        
        switch_active = 0
        if switch_on_value == 1:
            if switch_current_vaule == 1:
                switch_active = 1
        else:
            if switch_current_vaule == 0:
                switch_active = 1
                
        if switch_active == 1:
            if switch_function == "pump_schedule":
                body += output_text(">>>>>>>> Running Pump Schedule" + "")
                #Get Schedule
                with con:
                    
                    cur3 = con.cursor()    
                    cur3.execute("SELECT id, schedule_id, pump_id, add_ml FROM schedule")
                    while True:
                        row = cur3.fetchone()
                        
                        if row == None:
                            break

                        body += output_text("Schedule - row_id:" + str(row[0]) + " schedule_id" + str(row[1]) + " pump_id" + str(row[2]) + " add_ml" + str(row[3]) + "")
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
                        body += output_text(str(fill_seconds) + " " + str(add_ml) + " " + str(ml_per_minute) + "")
                        
                        body += output_text("Pumping" + str(pump_name) + "for " + str(fill_seconds) + "seconds" + "")
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
            elif switch_function == "email_fill_alert":
                body += output_text(">>>>>>>> Emailing Fill Alert" + "")
                fillmsg = MIMEMultipart()
                fillmsg['From'] = mailfrom
                fillmsg['To'] = mailto
                fillmsg['Subject'] = "Fill Alert on Switch " + datestamp
                body = "The fill switch has been triggered"
                fillmsg.attach(MIMEText(body, 'plain'))
                text = fillmsg.as_string()
                mailserver.sendmail(mailfrom, mailto, text)
            elif switch_function == "email_fill_emergency":
                body += output_text(">>>>>>>> Emailing Fill Emergency" + "")
                emergmsg = MIMEMultipart()
                emergmsg['From'] = mailfrom
                emergmsg['To'] = mailto
                emergmsg['Subject'] = "Fill Emergency on Switch " + datestamp
                body = "The emergency fill switch has been triggered"
                emergmsg.attach(MIMEText(body, 'plain'))
                text = emergmsg.as_string()
                mailserver.sendmail(mailfrom, mailto, text)

#Get Manual Pump Jobs
body += output_text("\r\n--------Getting Manual Pump Jobs--------")
with con:
    
    cur = con.cursor()    
    cur.execute("SELECT id, grow_id, pump_id, add_ml FROM grow_pump_manual WHERE in_progress = 0 AND completed != 1")
    while True:
        row = cur.fetchone()
        
        if row == None:
            break

        body += output_text("Manual Pump Job: " + str(row[0]) + " " + str(row[1]) + " " + str(row[2]) + " " + str(row[3]) + "")
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
        body += output_text(str(fill_seconds) + " " + str(add_ml) + " " + str(ml_per_minute) + "")
        
        body += output_text("Pumping" + str(pump_name) + "for" + str(fill_seconds) + "seconds" + "")
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
GPIO.cleanup()

msg = MIMEMultipart()
msg['From'] = mailfrom
msg['To'] = mailto
msg['Subject'] = "Autofeed - " + datestamp
msg.attach(MIMEText(body, 'plain'))
text = msg.as_string()
mailserver.sendmail(mailfrom, mailto, text)