function initMap() {
    // マップの初期化
    map = new google.maps.Map(document.getElementById('map'), _googleMapInitSetting);

    // クリックイベントを追加
    //map.addListener('click', function(e) {
    //    getClickLatLng(e.latLng, map);
    //});
    map.addListener('mouseup', function(e) {
        getClickLatLng(e.latLng, map);
    });

    // 初期位置にマーカーを設置
    var latitude = document.getElementById('lat').value;
    var longitude = document.getElementById('lng').value;
    var myLatlng = new google.maps.LatLng(latitude, longitude);
    var marker = new google.maps.Marker({
        position: myLatlng,
        map: map,
	    draggable:true
    });
    google.maps.event.trigger(map, 'resize');
}

function getClickLatLng(lat_lng, map) {
    // 座標を表示
    document.getElementById('lat').value = Math.round(lat_lng.lat()*100000)/100000;
    document.getElementById('lng').value = Math.round(lat_lng.lng()*100000)/100000;
    document.getElementById('zoom').value = map.getZoom();

    // 表示した座標をサーバに送信
    sendLocation(_scriptUrl,'#loc');

    // マーカーを設置
    var marker = new google.maps.Marker({
        position: lat_lng,
        map: map,
	draggable:true
    });

    // 座標の中心をずらす
    // http://syncer.jp/google-maps-javascript-api-matome/map/method/panTo/
    map.panTo(lat_lng);
    google.maps.event.trigger(map, 'resize');
}

function sendLocation(hostUrl, formId){
    var bSuccess = true;
    var params = $(formId).serialize();
    jQuery.ajax({
        url: hostUrl,
        type:'POST',
        dataType: 'json',
        data : params,
        timeout: 10000,
        success: function(data) {
            bSuccess = true;
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            bSuccess = false;
        }
    });
    return bSuccess;
}

function panCenter(){
    var w = parseInt(jQuery('#settings').css('width'));
    var th = parseInt(jQuery('#settings').css('height'));
    var h = window.innerHeight;
    jQuery('#map').css('width', w);
    jQuery('#map').css('height', h-th-40); //container paddin 20px
    var latitude = parseFloat(document.getElementById('lat').value);
    var longitude = parseFloat(document.getElementById('lng').value);
    var myLatlng = new google.maps.LatLng(latitude, longitude);
    google.maps.event.trigger(map, 'resize');
    map.panTo(myLatlng);
}

jQuery(window).load(function(){
    panCenter();
});

jQuery(window).resize(function(){
    panCenter();
});
