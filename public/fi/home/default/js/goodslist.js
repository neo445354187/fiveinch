$(function(){
	$('.goods_img2').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 100,placeholder:window.conf.ROOT+'/'+window.conf.GOODS_LOGO});//商品默认图片
	FI.dropDownLayer(".item",".dorp-down-layer");
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
});

function goodsFilter(obj,vtype){
	if(vtype==1){
		$('#brand').val($(obj).attr('v'));
	}else if(vtype==2){
		var price = $(obj).attr('v');
		price = price.split('_');
		$('#sprice').val(price[0]);
		$('#eprice').val(price[1]);
	}else if(vtype==3){
		$('#v_'+$(obj).attr('d')).val($(obj).attr('v'));
		var vs = $('#vs').val();
		vs = (vs!='')?vs.split(','):[];
		vs.push($(obj).attr('d'));
		$('#vs').val(vs.join(','));
	}
	var ipts = FI.getParams('.sipt');
	if(vtype==4)ipts['order']='1';
	var params = [];
	for(var key in ipts){
		if(ipts[key]!='')params.push(key+"="+ipts[key]);
	}
	location.href=FI.U('home/goods/lists',params.join('&'));
}
function goodsOrder(orderby){
	if($('#orderBy').val()!=orderby){
		$('#order').val(1);
	}
	$('#orderBy').val(orderby);
	goodsFilter(null,0);
}



function removeFilter(id){
	if(id!='price'){
		$('#'+id).val('');
		if(id.indexOf('v_')>-1){
			id = id.replace('v_','');
			var vs = $('#vs').val();
			vs = (vs!='')?vs.split(','):[];
			var nvs = [];
			for(var i=0;i<vs.length;i++){
				if(vs[i]!=id)nvs.push(vs[i]);
			}
			$('#vs').val(nvs.join(','));
		}
	}else{
		$('#sprice').val('');
		$('#eprice').val('');
	}
	var ipts = FI.getParams('.sipt');
	var params = [];
	for(var key in ipts){
		if(ipts[key]!='')params.push(key+"="+ipts[key]);
	}
	location.href=FI.U('home/goods/lists',params.join('&'));
}

/*搜索列表*/
function searchFilter(obj,vtype){
	if(vtype==1){
		$('#brand').val($(obj).attr('v'));
	}else if(vtype==2){
		var price = $(obj).attr('v');
		price = price.split('_');
		$('#sprice').val(price[0]);
		$('#eprice').val(price[1]);
	}else if(vtype==3){
		$('#v_'+$(obj).attr('d')).val($(obj).attr('v'));
		var vs = $('#vs').val();
		vs = (vs!='')?vs.split(','):[];
		vs.push($(obj).attr('d'));
		$('#vs').val(vs.join(','));
	}
	var ipts = FI.getParams('.sipt');
	if(vtype==4)ipts['order']='1';
	var params = [];
	for(var key in ipts){
		if(ipts[key]!='')params.push(key+"="+ipts[key]);
	}
	location.href=FI.U('home/goods/search',params.join('&'));
}
/**
 * [searchComponent 新的组装搜索条件]
 * @return {[type]}       [description]
 */
function searchComponent () {
	var ipts = FI.getParams('.sipt');
	var params = [];
	for(var key in ipts){
		if(ipts[key]!='')params.push(key+"="+ipts[key]);
	}
	location.href=FI.U('home/goods/search',params.join('&'));
}

/**
 * [searchOrderBy ]
 * @param  {[type]} $mark [description]
 * @return {[type]}       [description]
 */
function searchOrderBy($order_mark) {
	$('#orderBy').val($order_mark);
	var $upOrDown = $('#upOrDown'), $upOrDownVal = $upOrDown.val();
	if ($order_mark == 'price') {
		switch($upOrDownVal){
			case 'down':
				$upOrDown.val('up');
				break;
			default:
				$upOrDown.val('down');
				break;
		}
	} else{
		$upOrDown.val('');
	}
	searchComponent();
}

/**
 * [closeBrand 关闭选择的品牌]
 * @return {[type]} [description]
 */
function closeBrand() {
	$('#brand_name').val('');
	searchComponent();
}



function searchOrder(orderby){
	if($('#orderBy').val()!=orderby){
		$('#order').val(1);
	}
	$('#orderBy').val(orderby);
	searchFilter(null,0);
}


/*加入购物车*/
$('.goods').hover(function(){
	$(this).find('.sale-num').hide();
	$(this).find('.p-add-cart').show();
},function(){
	$(this).find('.sale-num').show();
	$(this).find('.p-add-cart').hide();
})



/*发货地*/
function gpanelOver(obj){
	var sid = $(obj).attr("id");

	var index = $(obj).attr('c');

	var ids = sid.split("_");
	var preid = ids[0]+"_"+ids[1];
	if(ids[2]==1){
		$("li[id^="+preid+"_]").hide();
		$("#"+sid).show();
	}else if(ids[2]==2){
		$('#fl_1_3').hide();
	}

	$("li[id^="+preid+"_]").removeClass("j-tab-selected"+index);
	$("#"+sid).addClass("j-tab-selected"+index);
	
	$("ul[id^="+preid+"_]").hide();
	$("#"+sid+"_pl").show();
}
function choiceArea(t,pid){
	var area_name = $(t).find('a').html();
	var parent = $(t).parent().attr('id');
	var ids = parent.split("_");
	var preid = "#"+ids[0]+"_"+ids[1]+"_"+ids[2];
	if(ids[2]==3){
		$(preid).find('a').html(area_name);
		// 执行发货地筛选
		$('#area_id').val(pid);
		var ipts = FI.getParams('.sipt');
		var params = [];
		for(var key in ipts){
			if(ipts[key]!='')params.push(key+"="+ipts[key]);
		}
		var url = ($(t).attr('search')==1)?'home/goods/search':'home/goods/lists';
		location.href=FI.U(url,params.join('&'));
	}else{
		// 替换当前选中地区
		$(preid).find('a').html(area_name);
		$(preid).removeClass('j-tab-selected'+ids[1]);


		var next = parseInt(ids[2])+1;
		var nextid = "#"+ids[0]+"_"+ids[1]+"_"+next;
		$(nextid).show();
		$(nextid).addClass("j-tab-selected"+ids[1]);
		// 替换下级地图标题
		$(nextid).html('<a href="javascript:void(0)">请选择</a>');

		// 获取下级地区信息
		$.post(FI.U('home/areas/listQuery'),{parent_id:pid},function(data){
			// 判断搜索页面
			var search = $(t).attr('search');
			if(search==1){search = 'search="1"';}
			
			var json = FI.toJson(data);
			if(json.status==1){
				var html = '';
				$(json.data).each(function(k,v){

					html +='<li onclick="choiceArea(this,'+v.area_id+')" '+search+' ><a href="javascript:void(0)">'+v.area_name+'</a></li>';
				});
				$(nextid+"_pl").html(html);
			}
		});

		// 隐藏当前地区,显示下级地区
		var preid = ids[0]+"_"+ids[1];
		$("ul[id^="+preid+"_]").hide();
		$(nextid+"_pl").show();
	}
}

