$(function(){
	// FI.dropDownLayer(".item",".dorp-down-layer");
	$('.item-more').click(function(){
		if($(this).attr('v')==1){
			$('.hideItem').show(300);
			$(this).find("span").html("收起");
			$(this).find("i").attr({"class":"drop-up"});
			$(this).attr('v',0);
		}else{
			$('.hideItem').hide(300);
			$(this).find("span").html("更多选项");
			$(this).find("i").attr({"class":"drop-down-icon"});
			$(this).attr('v',1);
		}
	});
	
	$(".item-more").hover(function(){
		if($(this).find("i").hasClass("drop-down-icon")){
			$(this).find("i").attr({"class":"down-hover"});
		}else{
			$(this).find("i").attr({"class":"up-hover"});
		}
		
	},function(){
		if($(this).find("i").hasClass("down-hover")){
			$(this).find("i").attr({"class":"drop-down"});
		}else{
			$(this).find("i").attr({"class":"drop-up"});
		}
	});
	if(goodsInfo.sku){
		var specs,dv;
		for(var key in goodsInfo.sku){
			if(goodsInfo.sku[key].is_default==1){
				specs = key.split(':');
				$('.j-option').each(function(){
					dv = $(this).attr('data-val')
					if($.inArray(dv,specs)>-1){
						$(this).addClass('j-selected');
					}
				})
				$('#buyNum').attr('data-max',goodsInfo.sku[key].spec_stock);
			}
		}
	}else{
		$('#buyNum').attr('data-max',goodsInfo.goods_stock);
	}
	checkGoodsStock();
	//图片放大镜效果
	CloudZoom.quickStart();
	imagesMove({id:'.goods-pics',items:'.items'});
	//选择规格
	$('.spec .j-option').click(function(){
		$(this).addClass('j-selected').siblings().removeClass('j-selected');
		checkGoodsStock();
	});
	$('#tab').TabPanel({tab:0,callback:function(no){}});
});

function checkGoodsStock(){
	var spec_ids = [],stock = 0,goods_price=0,market_price=0;
	if(goodsInfo.is_spec==1){
		$('.j-selected').each(function(){
			spec_ids.push(parseInt($(this).attr('data-val'),10));
		});
		spec_ids.sort(function(a,b){return a-b;});
		if(goodsInfo.sku[spec_ids.join(':')]){
			stock = goodsInfo.sku[spec_ids.join(':')].spec_stock;
			market_price = goodsInfo.sku[spec_ids.join(':')].market_price;
			goods_price = goodsInfo.sku[spec_ids.join(':')].spec_price;
		}
	}else{
		stock = goodsInfo.goods_stock;
		market_price = goodsInfo.market_price;
		goods_price = goodsInfo.goods_price;
	}
	$('#goods-stock').html(stock);
	$('#j-market-price').html('￥'+market_price);
	$('#j-shop-price').html('￥'+goods_price);
	if(stock<=0){
		$('#addBtn').addClass('disabled');
		$('#buyBtn').addClass('disabled');
	}else{
		$('#addBtn').removeClass('disabled');
		$('#buyBtn').removeClass('disabled');
	}
}

function imagesMove(opts){
	var tempLength = 0; //临时变量,当前移动的长度
	var viewNum = 5; //设置每次显示图片的个数量
	var moveNum = 2; //每次移动的数量
	var moveTime = 300; //移动速度,毫秒
	var scrollDiv = $(opts.id+" "+opts.items+" ul"); //进行移动动画的容器
	var scrollItems = $(opts.id+" "+opts.items+" ul li"); //移动容器里的集合
	var moveLength = scrollItems.eq(0).width() * moveNum; //计算每次移动的长度
	var countLength = (scrollItems.length - viewNum) * scrollItems.eq(0).width(); //计算总长度,总个数*单个长度
	  
	//下一张
	$(opts.id+" .next").bind("click",function(){
		if(tempLength < countLength){
			if((countLength - tempLength) > moveLength){
				scrollDiv.animate({left:"-=" + moveLength + "px"}, moveTime);
				tempLength += moveLength;
			}else{
				scrollDiv.animate({left:"-=" + (countLength - tempLength) + "px"}, moveTime);
				tempLength += (countLength - tempLength);
			}
		}
	});
	//上一张
	$(opts.id+" .prev").bind("click",function(){
		if(tempLength > 0){
			if(tempLength > moveLength){
				scrollDiv.animate({left: "+=" + moveLength + "px"}, moveTime);
				tempLength -= moveLength;
			}else{
				scrollDiv.animate({left: "+=" + tempLength + "px"}, moveTime);
				tempLength = 0;
			}
		}
	});
}


/****************** 商品评价 ******************/
function showImg(id){
  layer.photos({
      photos: '#img-file-'+id
    });
}
function queryByPage(p){
  var params = {};
  params.p = p;
  params.goods_id = goodsInfo.id;
  params.anonymous = 1;
  $.post(FI.U('home/goodsappraises/getById'),params,function(data,textStatus){
      var json = FI.toJson(data);
      if(json.status==1 && json.data.Rows){
          var gettpl = document.getElementById('tblist').innerHTML;
          laytpl(gettpl).render(json.data.Rows, function(html){
            $('#ga-box').html(html);
            for(var g=0;g<=json.data.Rows.length;g++){
              showImg(g);
            }
          });
          $('.j-lazyImg').lazyload({ effect: "fadeIn",failurelimit : 10,threshold: 200,placeholder:window.conf.ROOT+'/'+window.conf.GOODS_LOGO});
          if(json.totalPage>1){
            laypage({
               cont: 'pager', 
               pages:json.Total, 
               curr: json.CurrentPage,
               skin: '#e23e3d',
               groups: 3,
               jump: function(e, first){
                    if(!first){
                      queryByPage(e.curr);
                    }
                  } 
            });


          }else{
            $('#pager').empty();
          }
        }  
  });
}
function addCart(type,iptId){
	if(window.conf.IS_LOGIN==0){
		FI.loginWindow();
		return;
	}
	var goods_spec_id = 0;
	if(goodsInfo.is_spec==1){
		var spec_ids = [];
		$('.j-selected').each(function(){
			spec_ids.push($(this).attr('data-val'));
		});
		if(spec_ids.length==0){
			FI.msg('请选择你要购买的商品信息',{icon:2});
		}
		spec_ids.sort();
		if(goodsInfo.sku[spec_ids.join(':')]){
			goods_spec_id = goodsInfo.sku[spec_ids.join(':')].id;
		}
	}
	var buyNum = $(iptId)[0]?$(iptId).val():1;
	$.post(FI.U('home/carts/addCart'),{goods_id:goodsInfo.id,goods_spec_id:goods_spec_id,buyNum:buyNum,rnd:Math.random()},function(data,textStatus){
	     var json = FI.toJson(data);
	     if(json.status==1){
	    	 FI.msg(json.msg,{icon:1});
	    	 if(type==1){
	    		 location.href=FI.U('home/carts/index');
	    	 }
	     }else{
	    	 FI.msg(json.msg,{icon:2});
	     }
	});
}