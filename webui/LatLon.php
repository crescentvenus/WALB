<?php
$PH="/home/mars/WALB/IQ-files";
$DIR2="/var/www/html/pokePos";
$DIR="/home/mars/WALB/GPS-SDR-SIM_E";
$FILE="/tmp/fifo2";
$POWER=0;
$FREQ=1575420000;
$SAMPLE=2600000;
$DATE=trim(`LANG=C; date -u --date "18 sec" +%Y/%m/%d,%X`);
$BRDC="brdc2370.16n";
$N_SAT=16;
$FIFO="/tmp/fifo";
class Template{
    public function show($template_file_path, $compact){
        extract((array)$compact);
        include($template_file_path);
    }
};

function _file_put_contents($fname, $data) {
    if ($fp = fopen($fname, 'c')) {
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        fputs($fp, $data);
        fclose($fp);
    }
    return (bool)$fp;
}

$max_retry = 5;
$title = "gps spoof";
#$location_file = "LatLon.txt";
$location_file = "tmp.txt";
$fp=fopen($location_file,"r");
$in=fgets($fp);
fclose($fp);

if (file_exists($location_file)){
	$tmp=file($location_file);
	$t=explode(',',$tmp[0]);
	$settings["latitude"]=$t[0];
	$settings["longitude"]=$t[1];
    $settings["zoom"]=$t[3];
} else {
	$settings = [
    	"zoom" => 13,
    	"latitude" => 36.090725,
    	"longitude" => -115.175342,
	];
    // 初期値をファイルに書き出す
    $lat = round($settings['latitude'], 6);
    $lng = round($settings['longitude'], 6);
    $data = "{$lat},{$lng},100,{$settings['zoom']}\n";
    if ($fp = fopen($location_file, 'c')) {
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        fputs($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
if(isset($_POST['stop'])){
	exec("$DIR2/kill_proc.sh >/dev/null");
}
if(isset($_POST['start'])){
        exec("$DIR2/kill_proc.sh");
        $cmd1="/usr/local/bin/hackrf_transfer -t $FIFO -f $FREQ -s $SAMPLE -x $POWER  >/dev/null &";
        exec($cmd1);
        $cmd2="$DIR/gps-sdr-sim -s $SAMPLE -e $DIR/$BRDC -i $FILE -b8 -n $N_SAT -o $FIFO -T $DATE >/dev/null &";
        exec($cmd2);
        exec("$DIR2/smooth2.php >/dev/null &");
}
if(isset($_POST['latitude']) && isset($_POST['longitude'])){
    $ret = "true";
    $zoom = $_POST['zoom'];
    $lat = $_POST['latitude'];
    $lng = $_POST['longitude'];
    $lat = round($lat, 6);
    $lng = round($lng, 6);
    $data = "{$lat},{$lng},100,{$zoom}\n";

    if ($fp = fopen($location_file, 'c')) {
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        fputs($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);
    }else{
        $ret = "false";
    }

    echo '{"result":'.$ret.'}';
    return;
}

$template = new Template();
$template->show("LatLon.tmpl.php", compact('title','settings'));
