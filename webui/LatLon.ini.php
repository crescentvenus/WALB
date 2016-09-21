<?php
$FIFO="/tmp/fifo";
if(!file_exists($FIFO)){
        system("/usr/bin/mkfifo $FIFO");
}
$LatLon="/tmp/LatLon.txt";
$pointed_location=getcwd()."/LatLon.txt";
?>
