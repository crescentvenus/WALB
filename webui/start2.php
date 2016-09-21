#!/usr/bin/php
<?php
include("LatLon.ini.php");
$FILE=$LatLon;
//$FIFO="/tmp/fifo";
$DIR="/home/pi/gps-sdr-sim";
$POWER=0;
$FREQ=1575420000;
$SAMPLE=1100000;
$BRDC="brdc3640.15n"

$N_SAT=8;
$CWD=getcwd();
if($argv[1] =="stop"){
        exec("$CWD/kill_proc.sh &");
}
if($argv[1] =="start"){
        exec("$CWD/kill_proc.sh");
        sleep(1);
        $DATE=`LANG=C; date -u --date "18 sec" +%Y/%m/%d,%X`;
        $DATE=trim($DATE);
        $cmd2="$DIR/gps-sdr-sim -s $SAMPLE -e $DIR/$BRDC -L $FILE -b8 -n $N_SAT -o $FIFO -T $DATE >/dev/null &";
        exec($cmd2);
        sleep(1);
        exec("$CWD/smooth2.php >/dev/null &");
        $cmd1="/usr/local/bin/hackrf_transfer -t $FIFO -f $FREQ -s $SAMPLE -x 0 >/dev/null &";
        exec($cmd1);
}
?>
