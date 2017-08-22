#!/usr/bin/env python

import logging
import logging.handlers
import argparse
import sys
import sqlite3 as lite
import RPi.GPIO as GPIO
import time
import sys

# Deafults
LOG_FILENAME = "/tmp/hydrofeeder.log"
LOG_LEVEL = logging.INFO  # Could be e.g. "DEBUG" or "WARNING"

# Define and parse command line arguments
parser = argparse.ArgumentParser(description="Hydrofeeder Python service")
parser.add_argument("-l", "--log", help="file to write log to (default '" + LOG_FILENAME + "')")

# If the log file is specified on the command line then override the default
args = parser.parse_args()
if args.log:
        LOG_FILENAME = args.log

# Configure logging to log to a file, making a new file at midnight and keeping the last 3 day's data
# Give the logger a unique name (good practice)
logger = logging.getLogger(__name__)
# Set the log level to LOG_LEVEL
logger.setLevel(LOG_LEVEL)
# Make a handler that writes to a file, making a new file at midnight and keeping 3 backups
handler = logging.handlers.TimedRotatingFileHandler(LOG_FILENAME, when="midnight", backupCount=3)
# Format each log message like this
formatter = logging.Formatter('%(asctime)s %(levelname)-8s %(message)s')
# Attach the formatter to the handler
handler.setFormatter(formatter)
# Attach the handler to the logger
logger.addHandler(handler)

# Make a class we can use to capture stdout and sterr in the log
class MyLogger(object):
        def __init__(self, logger, level):
                """Needs a logger and a logger level."""
                self.logger = logger
                self.level = level

        def write(self, message):
                # Only log if there is a message (not just a new line)
                if message.rstrip() != "":
                        self.logger.log(self.level, message.rstrip())

# Replace stdout with logging to file at INFO level
sys.stdout = MyLogger(logger, logging.INFO)
# Replace stderr with logging to file at ERROR level
sys.stderr = MyLogger(logger, logging.ERROR)


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
while True:
    #Get Manual Pump Jobs
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

while True:
        logger.info("The counter is now " + str(i))
        
