/**
 * 高德地图涉及js
 */
var gd = {
        lnglat: lnglat,
        shop: shop_info
    },
    content = [],
    map = new AMap.Map('gd-container', {
        resizeEnable: true,
        zoomEnable: true,
        scrollWheel: false,
        center: gd.lnglat,
        zoom: 13
    }),
    marker = new AMap.Marker({
        position: gd.lnglat
    });
/*组合html*/
content.push('<div class="gd-info-title"><a href="" class="gd-shop-info">店铺：' + gd.shop.shop_name + '</a></div>');
content.push('<div class="gd-info-content">');
content.push('<img alt="' + gd.shop.shop_name + '" src="' + window.conf.ROOT + '/' + gd.shop.img + '" />');
content.push('<p title="' + gd.shop.catshop_names + '">经营：' + gd.shop.sub_catshop_names + '</p>');
content.push('<p title="' + gd.shop.address + '">地址：' + gd.shop.sub_address + '</p>');
content.push('<p>电话：' + gd.shop.telephone + '</p></div>');
var infowindow = new AMap.AdvancedInfoWindow({
    content: content.join(''),
    panel: 'gd-panel',
    autoMove: true,
    asOrigin: false,
    placeSearch: false,
    offset: new AMap.Pixel(0, -30)
});


/**
 * [redirect 跳转到高德地图]
 * @param  {[type]} marker [description]
 * @return {[type]}        [description]
 */
gd.redirect = function(marker) {
    marker.markOnAMAP({
        position: marker.getPosition(),
    })
}

marker.setMap(map);
infowindow.open(map, gd.lnglat);
infowindow.on('complete', function(SearchResult) {
    $("#gd-route").val('重新规划路线').show();
});
infowindow.on('error', function(ErrorStatus) {
    $("#gd-route").show();
    layer.msg("输入地址有误，请输入正确地址");
});
infowindow.on('close', function(ErrorStatus) {
    $("#gd-route").val('重新规划路线').show();
});
