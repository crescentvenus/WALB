#!/bin/sh
ipaddr=`LANG=C ifconfig wlan0 |grep "inet addr" |awk {'print $2'} |cut -f2 -d:`
addr_h=`echo ${ipaddr}|cut -f1,2 -d.`
addr_l=`echo ${ipaddr}|cut -f3,4 -d.`

case "$ipaddr" in
  "")
        i2c-disp.sh -i "none IP"
        ;;
  *)
        i2c-disp.sh -i ${addr_h}
        i2c-disp.sh .
        i2c-disp.sh -p 0x40  ${addr_l}
        ;;
esac