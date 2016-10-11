#!/usr/bin/php
<?php
require("LatLon.ini.php");
$current_pos=$LatLon;
//$pointed_location="/var/www/html/pokePos/LatLon.txt";
echo $pointed_location."\n";
echo $current_pos."\n";
$MAX_PTR=30000;                 // Maximum number of interpolation points.
$MAX_G=1*9.80665;               // 1 G
$R_earth=6378.1*1000;           // radius of the earth:6378Km
$Vdeg=1.2*360/(2*3.141592*$R_earth);
$dt=0.1;                        // 1/10 sec.
$SLEEP=100000;                  // 100mS

function update($msg,$current_pos){
//      echo $msg."\n";
        $fp_o=fopen($current_pos,"w");
        fputs($fp_o,$msg."\n");
        fclose($fp_o);
}

$fp_r=fopen($pointed_location,"r");
$dat=trim(fgets($fp_r));
fclose($fp_r);
$t=explode(",",$dat);   // Lat,Lon,ALt,Zoom,Max_speed
$x0=$t[0];$y0=$t[1];$z0=$t[2];
update($dat,$current_pos);
$prev=$dat;
$max_v=$t[4];
$max_deg_tic=$dt*$Vdeg*$max_v*1000/3600;
$offset = $max_deg_tic * 0.5;

while (1){
        $fp_r=fopen($pointed_location,"r");
        $dat=trim(fgets($fp_r));
        fclose($fp_r);
        $t=explode(",",$dat);   // Lat,Lon,ALt,Zoom,Max_speed
        $max_v=$t[4]*8 ;        // 
        $max_deg_tic=$dt*$Vdeg*$max_v*1000/3600;
        if($dat<>$prev) {               // New pointed Lat/Lon...speed
                echo "$dat,$prev\n";
                if(isset($buf)) {
                        $tmp=explode(",",$buf[$ptr]);
                        $x0=$tmp[0];$y0=$tmp[1];
                }
                $s=explode(",",$dat);
                $x=$s[0];$y=$s[1];$z=$s[2];
                $max_v=8*trim($t[4]);
                $max_deg_tic=$dt*$Vdeg*$max_v*1000/3600;
                $offset = $max_deg_tic * 0.5;
//              printf("$dat, MaxV:$max_v,Max_dev_tic:$max_deg_tic\n");
                $dx=($x0-$x);           // dintance to next position [deg]
                $dy=($y0-$y);
                $dx_s=($dx>0)?1:-1;
                $dy_s=($dy>0)?1:-1;
                $v0=0;$v=0;$tic=0;$nx=$x0;$ny=$y0;$i=0;
                $t=$dt;                         // update every 1/10 sec.
                if(abs($dx)>abs($dy)){
                        if($dx!=0){
                        $sc=abs($dy/$dx);
                                while(abs($x-$nx)>$offset){
                                        $v=($v>=$max_deg_tic)?$max_deg_tic:$v0+$MAX_G*$t*$Vdeg;
                                        $v0=$v;
                                        $dxl=$v*$t*$dx_s;
                                        $dyl=$v*$t*$dy_s;
                                        $nx=$nx-$dxl;
                                        $ny=$ny-$dyl*$sc;
                                        if(abs($y-$ny)<$offset) $ny=$y;
                                        $buf[$i]=$nx.",".$ny.",100";
                                        $i++;
                                        if($i>$MAX_PTR) break;
                                }
                        }
                } else {
                        if($dy!=0){
                                $sc=abs($dx/$dy);
                                while(abs($y-$ny)>$offset){
                                        $v=($v>=$max_deg_tic)?$max_deg_tic:$v0+$MAX_G*$t*$Vdeg;
                                        $v0=$v;
                        $dxl=$v*$t*$dx_s;
                        $dyl=$v*$t*$dy_s;
                        $nx=$nx-$dxl*$sc;
                                $ny=$ny-$dyl;
                                        if(abs($x-$nx)<$offset) $nx=$x;
                                        $buf[$i]=$nx.",".$ny.",100";
                                        $i++;
                                        if($i>$MAX_PTR) break;
                                }
                        }
                }
                echo "Number of points:$i\n";
                if($i>$MAX_PTR){            // Change location without interpolation when distance is too far away.
                        unset($buf);
                        $prev=$dat;
                        update($dat,$current_pos);
                } else {
                        $buf[$i++]=$dat;
                        $ptr_max=$i;
                        $ptr=0;$prev=$dat;
                        $x0=$x; $y0=$y; $z0=$z;
                        if (isset($buf)) update($buf[$ptr++],$current_pos);
                }
        } else {        // No current location changed
                if(isset($buf) && ($ptr<$ptr_max)){
                        update($buf[$ptr],$current_pos);
                        $ptr++;
                } else {
                        unset($buf);
                }
        }
        usleep($SLEEP);
}
?>
