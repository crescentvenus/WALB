#!/bin/sh
PH=/home/pi/IQ-files
DIR=/home/pi/gps-sdr-sim
FILE=$PH/$1
POWER=$2
FREQ=$3
SAMPLE=$4
DATE=$5

PID=`ps ax | grep hackrf_transfer | grep -v grep | awk '{print $1}'`
if  test -z $PID  ; then
if  [ $DATE = 'Now' ]; then
DATE=-T`LANG=C; date --date "18 sec" +%Y/%m/%d,%X`
elif  [ $DATE = 'ORG' ]; then
DATE=
elif [ $DATE = '3Y+' ]; then
DATE=-T`LANG=C; date --date "3 year 18 sec" +%Y/%m/%d,%X`
elif [ $DATE = '3Y-' ]; then
DATE=-T`LANG=C; date --date "3 year ago 18 sec" +%Y/%m/%d,%X`
elif [ $DATE = '1D+' ]; then
DATE=-T`LANG=C; date --date "1 day 18 sec" +%Y/%m/%d,%X`
elif  [ $DATE = '1D-' ]; then
DATE=-T`LANG=C; date --date "1 day ago 18 sec" +%Y/%m/%d,%X`
fi
echo $FILE, $POWER, $FREQ, $SAMPLE,$DATE
LINES=`wc $FILE -l | /usr/bin/awk '{print $1}'`
if [ $LINES = 1 ]; then
LOCATION=`cat $FILE`
echo "Fix Location"
$DIR/gps-sdr-sim -e $DIR/brdc3640.15n -l $LOCATION -b8 -o $DIR/fifo $DATE&
elif [ `echo $FILE | grep csv` ]; then
echo "csv"
cat $FILE | $DIR/gps-sdr-sim -e $DIR/brdc3640.15n -u- -b8 -o $DIR/fifo $DATE&
elif [ ` echo $FILE | grep txt` ]; then
echo "NMEA"
cat $FILE | $DIR/gps-sdr-sim -e $DIR/brdc3640.15n -g- -b8 -o $DIR/fifo $DATE&
fi

/usr/l