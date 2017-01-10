$(document).ready(function() {
    var cartHeight = FI.pageHeight() - 120;
    $('.toolbar-tab').hover(function() {
        $(this).find('.tab-text').addClass("tbar-tab-hover");
        $(this).find('.footer-tab-text').addClass("tbar-tab-footer-hover");
        $(this).addClass("tbar-tab-selected");
    }, function() {
        $(this).find('.tab-text').removeClass("tbar-tab-hover");
        $(this).find('.footer-tab-text').removeClass("tbar-tab-footer-hover");
        $(this).removeClass("tbar-tab-selected");
    });
    $('.j-close').click(function() {
        if ($('.toolbar-wrap').hasClass('toolbar-open')) {
            $('.toolbar-wrap').removeClass('toolbar-open');
        } else {
            $('.toolbar-wrap').addClass('toolbar-open');
        }
    })
    $('.j-global-toolbar').siblings().click(function() {
        if ($('.toolbar-wrap').hasClass('toolbar-open')) {
            $('.toolbar-wrap').removeClass('toolbar-open');
        }
    })
    $('.tbar-tab-cart').click(function() {
        if ($('.toolbar-wrap').hasClass('toolbar-open')) {
            if ($(this).find('.tab-text').length > 0) {
                if (!$('.tbar-tab-follow').find('.tab-text').length > 0) {
                    var info = "<em class='tab-text '>我的关注</em>";
                    $('.tbar-tab-follow').append(info);
                    $('.tbar-tab-follow').removeClass('tbar-tab-click-selected');
                    $('.tbar-panel-follow').css({ 'visibility': "hidden", "z-index": "-1" });
                }
                if (!$('.tbar-tab-history').find('.tab-text').length > 0) {
                    var info = "<em class='tab-text '>我的足迹</em>";
                    $('.tbar-tab-history').append(info);
                    $('.tbar-tab-history').removeClass('tbar-tab-click-selected');
                    $('.tbar-panel-history').css({ 'visibility': "hidden", "z-index": "-1" });
                }
                $(this).addClass('tbar-tab-click-selected');
                $(this).find('.tab-text').remove();
                $('.tbar-panel-cart').css({ 'visibility': "visible", "z-index": "1" });
                getRightCart();
            } else {
                var info = "<em class='tab-text '>我的关注</em>";
                $('.toolbar-wrap').removeClass('toolbar-open');
                $(this).append(info);
                $(this).removeClass('tbar-tab-click-selected');
                $('.tbar-panel-cart').css({ 'visibility': "hidden", "z-index": "-1" });
            }
        } else {
            $(this).addClass('tbar-tab-click-selected');
            $(this).find('.tab-text').remove();
            $('.tbar-panel-cart').css({ 'visibility': "visible", "z-index": "1" });
            $('.tbar-panel-follow').css('visibility', 'hidden');
            $('.tbar-panel-history').css('visibility', 'hidden');
            $('.toolbar-wrap').addClass('toolbar-open');
            $('#cart-panel').css('height', cartHeight + "px").css('overflow-y', 'auto');
            getRightCart();
        }
    });
    $('.tbar-tab-follow').click(function() {
        if ($('.toolbar-wrap').hasClass('toolbar-open')) {
            if ($(this).find('.tab-text').length > 0) {
                if (!$('.tbar-tab-cart').find('.tab-text').length > 0) {
                    var info = "<em class='tab-text '>购物车</em>";
                    $('.tbar-tab-cart').append(info);
                    $('.tbar-tab-cart').removeClass('tbar-tab-click-selected');
                    $('.tbar-panel-cart').css({ 'visibility': "hidden", "z-index": "-1" });
                }
                if (!$('.tbar-tab-history').find('.tab-text').length > 0) {
                    var info = "<em class='tab-text '>我的足迹</em>";
                    $('.tbar-tab-history').append(info);
                    $('.tbar-tab-history').removeClass('tbar-tab-click-selected');
                    $('.tbar-panel-history').css({ 'visibility': "hidden", "z-index": "-1" });
                }
                $(this).addClass('tbar-tab-click-selected');
                $(this).find('.tab-text').remove();
                $('.tbar-panel-follow').css({ 'visibility': "visible", "z-index": "1" });

            } else {
                var info = "<em class='tab-text '>我的关注</em>";
                $('.toolbar-wrap').removeClass('toolbar-open');
                $(this).append(info);
                $(this).removeClass('tbar-tab-click-selected');
                $('.tbar-panel-follow').css({ 'visibility': "hidden", "z-index": "-1" });
            }


        } else {
            $(this).addClass('tbar-tab-click-selected');
            $(this).find('.tab-text').remove();
            $('.tbar-panel-cart').css('visibility', 'hidden');
            $('.tbar-panel-follow').css({ 'visibility': "visible", "z-index": "1" });
            $('.tbar-panel-history').css('visibility', 'hidden');
            $('.toolbar-wrap').addClass('toolbar-open');
        }
    });
    $('.tbar-tab-history').click(function() {
        if ($('.toolbar-wrap').hasClass('toolbar-open')) {
            if ($(this).find('.tab-text').length > 0) {
                if (!$('.tbar-tab-follow').find('.tab-text').length > 0) {
                    var info = "<em class='tab-text '>我的关注</em>";
                    $('.tbar-tab-follow').append(info);
                    $('.tbar-tab-follow').removeClass('tbar-tab-click-selected');
                    $('.tbar-panel-follow').css({ 'visibility': "hidden", "z-index": "-1" });
                }
                if (!$('.tbar-tab-cart').find('.tab-text').length > 0) {
                    var info = "<em class='tab-text '>购物车</em>";
                    $('.tbar-tab-cart').append(info);
                    $('.tbar-tab-cart').removeClass('tbar-tab-click-selected');
                    $('.tbar-panel-cart').css({ 'visibility': "hidden", "z-index": "-1" });
                }
                $(this).addClass('tbar-tab-click-selected');
                $(this).find('.tab-text').remove();
                $('.tbar-panel-history').css({ 'visibility': "visible", "z-index": "1" });
                getHistoryGoods();
            } else {
                var info = "<em class='tab-text '>我的足迹</em>";
                $('.toolbar-wrap').removeClass('toolbar-open');
                $(this).append(info);
                $(this).removeClass('tbar-tab-click-selected');
                $('.tbar-panel-history').css({ 'visibility': "hidden", "z-index": "-1" });
            }

        } else {
            $(this).addClass('tbar-tab-click-selected');
            $(this).find('.tab-text').remove();
            $('.tbar-panel-cart').css('visibility', 'hidden');
            $('.tbar-panel-follow').css('visibility', 'hidden');
            $('.tbar-panel-history').css({ 'visibility': "visible", "z-index": "1" });
            $('.toolbar-wrap').addClass('toolbar-open');
            getHistoryGoods();
        }
    });
    //存储返回的地址信息
    var $location_content = '',
        $is_cache = false; //判断是否缓存，以便决定是否请求数据

    $('#user_location').click(function() {
        if (!$is_cache) {
            $.ajax({
                url: FI.U("home/areas/getProvincesAndCities"),
                type: 'POST',
                data_type: 'json',
                data: {},
                success: function($json) {
                    if ($json.status == 1) {
                    	$is_cache = true;
                        $location_content = $json.data;
                        layer_location($location_content);
                    } else {
                        layer.msg('获取地址失败', { icon: 2 });
                    }
                },
                error: function($json) {
                    layer.msg('获取地址失败', { icon: 2 });
                }
            })
        }
        if ($location_content != '') {
        	layer_location($location_content);
        }
    })


});
/**
 * [layer_location 用layer弹框]
 * @param  {[type]} $location_content [description]
 * @return {[type]}                   [description]
 */
function layer_location ($location_content) {
	layer.open({
	    type: 1,
	    title: ['请选择地址', 'font-size:18px;text-align:center;'],
	    skin: 'layui-layer-demo', //样式类名
	    closeBtn: 0, //不显示关闭按钮
	    anim: 2,
	    area: ['800px', '500px'],
	    shadeClose: true, //开启遮罩关闭
	    content: $location_content
	});
}

/**
 * [setLocation 设置用户地址]
 * @param {[type]} $province_id [description]
 * @param {[type]} $city_id [description]
 */
setLocation = function($this, $province_id, $city_id) {
    $.ajax({
        url: FI.U("home/areas/setLocation"),
        type: 'POST',
        data_type: 'json',
        data: { province_id: $province_id, city_id: $city_id },
        success: function($json) {
        	var $text = $($this).text();
            $('#user_location span').text('【'+$text.substr(0, 5)+'】').attr('title', $text);
            layer.closeAll();
            layer.msg('设置地址成功', { icon: 1 });
        },
        error: function($json) {
            layer.closeAll();
            layer.msg('设置地址失败', { icon: 2 });
        }
    })


}




function getRightCart() {
    $.post(FI.U('home/carts/getCart'), '', function(data) {
        var json = FI.toJson(data);
        if (json.status == 1) {
            json = json.data;
            if (json.carts && json.goodsTotalNum > 0) {
                var gettpl = document.getElementById('list-rightcart').innerHTML;
                laytpl(gettpl).render(json.carts, function(html) {
                    $('#cart-panel').html(html);
                });
                $('#j-goods-count').html(json.goodsTotalNum);
                $('#j-goods-total-money').html(json.goodsTotalMoney);
            } else {
                $('#cart-panel').html('<p class="right-carts-empty">购物车空空如也，赶紧去选购吧～</p>');
            }
        }
    });
}

function delRightCart(obj, id) {
    var dataval = $(obj).attr('dataid');
    dataval = dataval.split("|");
    if ($('#shop-cart-' + dataval[0]).children().size() > 2) {
        $('.j-goods-item-' + dataval[1]).remove();
    } else {
        $('#shop-cart-' + dataval[0]).remove();
    }
    statRightCartMoney();
    $.post(FI.U('home/carts/delCart'), { id: dataval[1], rnd: Math.random() }, function(data, textStatus) {
        var json = FI.toJson(data);
        if (json.status != 1) {
            FI.msg(json.msg, { icon: 2 });
        }
    });
}

function jumpSettlement() {
    if ($('#cart-panel').children().size() == 0) {
        FI.msg("您的购物车没有商品哦，请先添加商品~", { icon: 2 });
        return;
    }
    location.href = FI.U('home/carts/settlement');
}

function getHistoryGoods() {
    $.post(FI.U('home/goods/historyByGoods'), {}, function(data) {
        var json = FI.toJson(data);
        if (json.status == 1) {
            var gettpl = document.getElementById('list-history-goods').innerHTML;
            laytpl(gettpl).render(json.data, function(html) {
                $('#history-goods-panel').html(html);
            });
            $('.jth-item').hover(function() { $(this).find('.add-cart-button').show(); }, function() { $(this).find('.add-cart-button').hide(); });
        }
    });
}

function statRightCartMoney() {
    var cart_id, goods_num = 0,
        goods_money = 0,
        tmpGoodsNum = 0,
        tmpGoodsMoney = 0;
    $('.jtc-item-goods').each(function() {
        cart_id = $(this).attr('dataval');
        goods_num = parseInt($('#buyNum_' + cart_id).val(), 10);
        goods_money = parseFloat($('#gprice_' + cart_id).html(), 10);
        tmpGoodsNum++;
        tmpGoodsMoney += goods_money * goods_num;
    })
    var goodsTotalNum = parseInt($('#j-goods-count').html(), 10);
    var goodsTotalMoney = parseFloat($('#j-goods-total-money').html(), 10);
    if (goodsTotalNum == 0) {
        $('#j-goods-count').html(0);
        $('#j-goods-total-money').html(0);
    } else {
        $('#j-goods-count').html(tmpGoodsNum);
        $('#j-goods-total-money').html(tmpGoodsMoney);
    }
}
