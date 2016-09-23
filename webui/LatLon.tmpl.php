<!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php echo $title ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Expires" content="<?= gmdate('D, d M Y H:i:s').' GMT' ?>">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="LatLon.css" rel="stylesheet">
</head>
<body>
    <div  id="body" class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-body">
                <table><tr><td class="form-array">
                    <div class="form-group form-inline">
                    <form id="loc" method="post" accept-charset="utf-8">
                        <label for="text">Location: </label>
                        <input type="hidden" id="zoom" name="zoom" value="<?php echo $settings['zoom'] ?>">
                        <input id="lat" type="text" name="latitude" size="8"  placeholder="Latitude..." value="<?php echo $settings['latitude']?>"/>
                        <input id="lng" type="text" name="longitude" size="8" placeholder="Longitude..." value="<?php echo $settings['longitude']?>"/>

                        <label for="text">Max Speed: </label>
                                                <select name="speed">
                                                        <?php $speed=$settings['speed']?>
                                                        <option value="40"   <?php if($speed==40)   echo selected ?>>5Km</option>
                                                        <option value="80"   <?php if($speed==80)   echo selected ?>>10Km</option>
                                                        <option value="400"  <?php if($speed==400)  echo selected ?>>50Km</option>
                                                        <option value="800"  <?php if($speed==800)  echo selected ?>>100Km</option>
                                                        <option value="1600" <?php if($speed==1600) echo selected ?>>200Km</option>
                                                        <option value="2400" <?php if($speed==2400) echo selected ?>>300Km</option>
                                                </select>
<!--
                        <input id="sub" type="button" value="Submit"
                        onClick="sendLocation('http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']?>','#loc');return false;">
-->
                    </form>
                    </div>
                </td><td class="form-array">
                    <div class="form-group form-inline">
                    <form id="SIM" method="POST" action="LatLon.php">
                                        <input id="start" type="submit" value="SimStart" name="start">
                                        <input id="stop" type="submit" value="SimStop" name="stop">
                        </form>
                    </div>
                </td></tr><table>
                <div id="map" style="width:1000px;height:700px;"></div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    // initailzation
    _scriptUrl = 'http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']?>';
    _googleMapInitSetting =
        {
            zoom: <?php echo $settings['zoom'] ?>,
                        scaleControl: true,
            center: {
                lat: <?php echo $settings['latitude'] ?>,
                lng:<?php echo $settings['longitude'] ?>
            }
        };
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="./LatLon.js"></script>
    <script src="http://maps.googleapis.com/maps/api/js?signed_in=true&callback=initMap"></script>
</body>
</html>
