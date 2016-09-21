#!/usr/bin/php
<?php
$PH="/home/mars/WALB/IQ-files";
$DIR="/home/mars/WALB/GPS-SDR-SIM_E";
$FILE="/tmp/fifo2";
$POWER=0;
$FREQ=1575420000;
$SAMPLE=2600000;
$DATE=`LANG=C; date -u --date "18 sec" +%Y/%m/%d,%X`;
$DATE=trim($DATE);
$BRDC="brdc2370.16n";
$N_SAT=16;
$FIFO="/tmp/fifo";
if($argv[1] =="stop"){
	exec("./kill_proc.sh &");
}
if($argv[1] =="start"){
	echo "Sim starting.....<BR>\n";
	exec("./kill_proc.sh");
	echo "Sim kill_proc.....<BR>\n";
	$cmd2="$DIR/gps-sdr-sim -s $SAMPLE -e $DIR/$BRDC -i $FILE -b8 -n $N_SAT -o $FIFO -T $DATE >/dev/null &";
	echo "$cmd2\n";
	exec($cmd2);
	echo "Sim sps-sdr-sim.....<BR>\n";
	$cmd1="/usr/local/bin/hackrf_transfer -t /tmp/fifo -f 1575420000 -s 2600000 -x 0 >/dev/null &";
	echo "$cmd1\n";
	exec($cmd1);
	echo "Sim hackrf....<BR>\n";
}
?>
