<?php
require_once("LatLon.ini.php");
$location_file=$pointed_location;
$cwd=getcwd();

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
$fp=fopen($location_file,"r");
$in=fgets($fp);
fclose($fp);

if (file_exists($location_file)){
        $tmp=file($location_file);
        $t=explode(',',$tmp[0]);
        $settings["latitude"]=$t[0];
        $settings["longitude"]=$t[1];
    $settings["zoom"]=$t[3];
        $settings["speed"]=$t[4];
} else {
        $settings = [
                "speed" => 40,
        "zoom" => 13,
        "latitude" => 36.090725,
        "longitude" => -115.175342,
        ];
    // write initial location to file
    $lat = round($settings['latitude'], 6);
    $lng = round($settings['longitude'], 6);
    $data = "{$lat},{$lng},100,{$settings['zoom']},{$settings['speed']}\n";
    if ($fp = fopen($location_file, 'c')) {
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        fputs($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
if(isset($_POST['stop'])){
        exec("$cwd/start2.php stop ");
}
if(isset($_POST['start'])){
        exec("$cwd/start2.php start");
}
if(isset($_POST['latitude']) && isset($_POST['longitude'])){
    $ret = "true";
    $zoom = $_POST['zoom'];
        $speed=$_POST['speed'];
    $lat = $_POST['latitude'];
    $lng = $_POST['longitude'];
    $lat = round($lat, 6);
    $lng = round($lng, 6);
    $data = "{$lat},{$lng},100,{$zoom},{$speed}\n";

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
$template->show("LatLon.tmpl2.php", compact('title','settings'));
