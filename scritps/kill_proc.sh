#!/bin/sh
PID=`ps ax | grep gps-sdr-sim | grep -v grep | awk '{print $1}'`
if  test -z $PID  ; then
        echo "No such a process"
else
        echo $PID
        kill -6 $PID
fi
PID=`ps ax | grep hackrf | grep -v grep | awk '{print $1}'`
if  test -z $PID  ; then
        echo "No such a process"
else
        echo $PID
        kill -6 $PID
fi