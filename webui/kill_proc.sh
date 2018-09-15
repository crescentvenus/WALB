#!/bin/sh
for proc in smooth2.php hackrf  gps-sdr-sim
do
        PID=`ps ax | grep $proc | grep -v "sh -c " | grep -v grep | awk '{print $1}'`
        if  test -z $PID  ; then
                echo "No such a process"
        else
                echo $PID
                kill -6 $PID
        fi
done
