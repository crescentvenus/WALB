#!/bin/bash
PH=/home/pi/ADS-B_signal_generator
PID=`ps ax | grep hackrf_transfer | grep -v grep | awk '{print $1}'`
if  test -z $PID  ; then
$PH/ads-b_gen0 -i $PH/aug02.raw -m 1 -o /tmp/fifo >/dev/null&
hackrf_transfer -f 1090000000 -s 2000000 -x 6 -t /tmp/fifo >/dev/null&
fi
