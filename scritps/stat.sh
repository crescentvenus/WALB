#!/bin/sh
STAT=`ps ax | grep  hackrf | grep -v grep`
#echo $STAT
if [ -n "$STAT" ]; then
        RET=1
else
        RET=0
fi
return $RET