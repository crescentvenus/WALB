#!/usr/bin/php
<?php
function update($msg,$fp){
//	echo $msg."\n";
	$fp_o=fopen("/tmp/fifo2","w");
	fputs($fp_o,$msg."\n");
	fclose($fp_o);
}
$MAX=200;
$SLEEP=100000;
$prev=0;
$Gmax=1;
$g=9.8;
$Max_step=$Gmax*$g*360/(2*3.141*6371*1000);	// m/step speed of movement
$VmaxS=300*1000/3600;				// 300Km/Hour Max speed
$Tmax=$VmaxS/$g;				// Sec  Time to reach MaxSpeed;

$fp=fopen("/tmp/tmp.txt","w");
fputs("36.112951,-115.175643,100.0\n");
fclose($fp);
system("chmod 666 /tmp/tmp.txt");
while (1){
	$fp_r=fopen("/tmp/tmp.txt","r");
	$dat=trim(fgets($fp_r));
	fclose($fp_r);
	if($prev == 0){
		update($dat,$fp);
		$prev=$dat;
		$t=explode(",",$dat);
		$x0=$t[0];$y0=$t[1];$z0=$t[2];
		$ptr=$MAX;
	} else {
		if($dat<>$prev) {
			printf("$dat.\n");
			$t=explode(",",$dat);
			$x=$t[0];$y=$t[1];$z=$t[2];
			$dx=($x0-$x)/$Max_step;	// number of steps to move the path
			$nx=$x0;$sx=gmp_sign($dx);
			$Tx1=$Tmax;$Tx2=
			$dy=($y0-$y)/$Max_step;$ny=$y0;$sy=gmp_sign($dy);
			$dz=($z0-$z)/$Max_step;
			if(abs($dx)>$abs($dy)){
				if(abs($dx))>2*$Tmax){	// reach max speed /---\
					$T=0;
					$V=0;
					while(1){
						if($T>$Tmax
						$nx+=$sx*$Max_step;
						$ny+=$sy*$Max_step;
				} else {	//not reach to max speed

				}
			} 
			for($i=0;$i<$MAX;$i++){
				$nx=round($x0-$dx*($i+1),6);
				$ny=round($y0-$dy*($i+1),6);
				$nz=round($z0-$dz*($i+1),0);
				$buf[$i]=$nx.",".$ny.",".$nz;
//				printf ("$nx,$ny,$nz\n");
			}
			$ptr=0;$prev=$dat;
			$x0=$x; $y0=$y; $z0=$z;
			update($buf[$ptr++],$fp);
		} else {
			if($ptr<$MAX){
				update($buf[$ptr],$fp);
				$ptr++;
			}
		}
	}
	usleep($SLEEP);
}
?>
		

