//关注的商品列表
function freGoodsList(pages){
	var param = {};
	param.pagesize = 8;
	param.page = pages;
    $.post(FI.U('home/favorites/listGoodsQuery'),param,function(data){
        var json = FI.toJson(data);
        if(json.status==1){
        	json = json.data;
	        var gettpl = document.getElementById('list').innerHTML;
	        laytpl(gettpl).render(json.Rows, function(html){
	            $('#list-goods').html(html);
	        });
	        if(json.TotalPage>1){
	            laypage({
	               cont: 'goodsPage',
	               pages:json.TotalPage, 
	               curr: json.CurrentPage,
	               skip: true, //是否开启跳页
	               skin: '#f46442',
	               groups: 3,
	               prev: '<<',
	               next: '>>',
	               jump: function(e, first){
	                    if(!first){
	                    	freGoodsList(e.curr);
	                    }
	                  } 
	            });
	        }else{
	            $('#goodsPage').empty();
	        }
	    	$(".fi-fav-goimg").hover(function(){
	    		$(this).find(".js-operate").slideDown();
	    	},function(){
	    		$(this).find(".js-operate").slideUp();
	    	});
	    	$('.goodsImg2').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+window.conf.GOODS_LOGO});//商品默认图片
        }
    });
}
function getGoods(id){
	location.href=FI.U('home/goods/detail','id='+id);
}
function cancelFavorite(id,type){
	FI.confirm({content:"您确定要取消关注吗？", yes:function(tips){
	    var load = FI.load({msg:'请稍后...'});
		var param = {};
		param.id = id;
		param.type = type;
	    $.post(FI.U('home/favorites/cancel'),param,function(data,textStatus){
	      layer.close(load);
	      var json = FI.toJson(data);
	      if(json.status=='1'){
	        FI.msg(json.msg,{icon:1},function(){
	        	if(type==0){
	        		freGoodsList();
	        	}else{
	        		freShopList();
	        	}
	        	
	        });
	      }else{
	        FI.msg(json.msg,{icon:5});
	      }
	    });
	}});
}
//关注的店铺列表
function freShopList(pages){
	var param = {};
	param.pagesize = 3;
	param.page = pages;
    $.post(FI.U('home/favorites/listShopQuery'),param,function(data){
        var json = FI.toJson(data);
        if(json.status==1){
        	json = json.data;
	        var gettpl = document.getElementById('list').innerHTML;
	        laytpl(gettpl).render(json.Rows, function(html){
	            $('#list-shops').html(html);
	        });
	        //商品滑动
	    	var goodsNum = json.Rows.length;
	    	for(var i=0;i<goodsNum;++i){
		    	$("#js-goods"+i).als({
		    		visible_items: 5,
		    		scrolling_items: 1,
		    		orientation: "horizontal",
		    		circular: "yes",
		    		autoscroll: "no",
		    		start_from: 2
		    	});
	    	}
	    	$('.goodsImg2').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+window.conf.GOODS_LOGO});//商品默认图片
	        $('.shopsImg2').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+window.conf.SHOP_LOGO});//店铺默认头像
	        if(json.TotalPage>1){
	            laypage({
	               cont: 'shopsPage',
	               pages:json.TotalPage, 
	               curr: json.CurrentPage,
	               skip: true, //是否开启跳页
	               skin: '#f46442',
	               groups: 3,
	               prev: '<<',
	               next: '>>',
	               jump: function(e, first){
	                    if(!first){
	                    	freShopList(e.curr);
	                    }
	                  } 
	            });
	        }else{
	            $('#shopsPage').empty();
	        }
        }
    });
}
function getShop(id){
	location.href=FI.U('home/shops/home','shopId='+id);
}