#!/usr/bin/php
<?php
include("LatLon.ini.php");
$FILE=$LatLon;

$DIR="/home/pi/bin";
$POWER=0;
$FREQ=1575420000;
$SAMPLE=2048000;
$BRDC="brdc3640.15n";
$N_SAT=16;
$CWD=getcwd();
if($argv[1] =="stop"){
        exec("$CWD/kill_proc.sh &");
}
if($argv[1] =="start"){
        exec("$CWD/kill_proc.sh");
        sleep(1);
        $DATE=`LANG=C; date -u --date "18 sec" +%Y/%m/%d,%X`;
        $DATE=trim($DATE);
        $cmd2="$DIR/gps-sdr-sim -s $SAMPLE -e $DIR/$BRDC -i $FILE -b8 -n $N_SAT -o $FIFO -T $DATE >/dev/null &";
        exec($cmd2);
        sleep(1);
        exec("$CWD/smooth2.php >/dev/null &");
        $cmd1="/usr/local/bin/hackrf_transfer -t $FIFO -f $FREQ -s $SAMPLE -x 0 >/dev/null &";
        exec($cmd1);
}
?>
