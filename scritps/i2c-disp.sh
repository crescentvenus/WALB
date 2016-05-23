#!/bin/bash
function usage {
    echo "Usage: $0 [-ic] [-p pos] message" > /dev/stderr;
    exit 1
}
[ $# = 0 ] && usage

while getopts "icp:" flag; do
    case $flag in
        \?) usage ;;
        i)  i2cset -y 1 0x3e 0 0x38 0x39 0x14 0x70 0x56 0x6c i
            sleep 0.25
            i2cset -y 1 0x3e 0 0x0c 0x01 0x06 i
            sleep 0.05
            ;;
        c)  i2cset -y 1  0x3e 0 0x01 ;;
        p)  i2cset -y 1 0x3e 0 $((OPTARG+128)) ;;
    esac
done
shift $((OPTIND-1))
[ $# = 0 ] && exit

LANG=C
MSG=`echo -n "$1" | perl -pe '$_=join" ",map{ord }split//'`
#echo $MSG
i2cset -y 1 0x3e 0x40 $MSG i