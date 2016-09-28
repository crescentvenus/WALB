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
    <link href="LatLon.css" rel="stylesheet">
</head>
<body>
    <div id="body" class="container">
        <table id="settings" class="fluid">
            <tr><td class="align-left">
                <form id="loc" method="post" accept-charset="utf-8">
                    <label class="text-bold"> Location: </label>
                    <input type="hidden" id="zoom" name="zoom" value="<?php echo $settings['zoom'] ?>">
                    <input id="lat" type="text" name="latitude" class="form-control text-red text-bold" placeholder="Latitude..." value="<?php echo $settings['latitude']?>"/>
                    <input id="lng" type="text" name="longitude" class="form-control text-red text-bold" placeholder="Longitude..." value="<?php echo $settings['longitude']?>"/>

                    <label for="text">Max Speed: </label>
                    <select name="speed">
                        <?php $speed=$settings['speed']?>
                        <option value="5"   <?php if($speed==5)   echo selected ?>>5Km</option>
                        <option value="10"  <?php if($speed==10)  echo selected ?>>10Km</option>
                        <option value="50"  <?php if($speed==50)  echo selected ?>>50Km</option>
                        <option value="100" <?php if($speed==100) echo selected ?>>100Km</option>
                        <option value="200" <?php if($speed==200) echo selected ?>>200Km</option>
                        <option value="300" <?php if($speed==300) echo selected ?>>300Km</option>
                    </select>

                    <!--
                    <input id="sub" type="button" value="Submit" class="form-control form-button text-bold"
                    onClick="sendLocation('http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']?>','#loc');return false;">
                    -->
                </form>
            </td><td class="align-right">
                <form id="SIM" method="POST" action="LatLon.php">
                    <input id="start" type="submit" value="SimStart" name="start" class="form-control form-button text-red text-bold">
                    <input id="stop" type="submit" value="SimStop" name="stop" class="form-control form-button text-red text-bold">
                </form>
            </td></tr>
        <table>
        <div id="map" style="width:200px;height:200px;"></div>
    </div>

    <script type="text/javascript">
    // initailzation
    _scriptUrl = 'http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']?>';
    _googleMapInitSetting =
        {
            zoom: <?php echo $settings['zoom'] ?>,
            center: {
                lat: <?php echo $settings['latitude'] ?>,
                lng:<?php echo $settings['longitude'] ?>
            }
        };
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="./LatLon.js"></script>
    <script src="http://maps.googleapis.com/maps/api/js?signed_in=true&callback=initMap"></script>
</body>
</html>
