function moveRight(suffix){
	$('input[name="lchk'+suffix+'"]:checked').each(function(){
		var html = [];
		html.push('<div class="trow"><div class="tck"><input type="checkbox" name="rchk'+suffix+'" class="rchk'+suffix+'" value="'+$(this).val()+'"></div>');
		html.push('<div class="ttxt">'+$(this).parent().parent().find('.ttxt').html()+'</div>');
		html.push('<div class="top"><input type="text" class="s-sort s-ipt'+suffix+'" value="0" v="'+$(this).val()+'"></div></div>');
		$(this).parent().parent().remove();
		$('#rlist'+suffix).append(html.join(''));
	});
	var ids = [];
	$('input[name="rchk'+suffix+'"]').each(function(){
		ids.push($(this).val());
	});
	$('#ids'+suffix).val(ids.join(','));
}
function moveLeft(suffix){
	$('input[name="rchk'+suffix+'"]:checked').each(function(){
		var html = [];
		html.push('<div class="trow"><div class="tck"><input type="checkbox" name="lchk'+suffix+'" class="lchk'+suffix+'" value="'+$(this).val()+'"></div>');
		html.push('<div class="ttxt">'+$(this).parent().parent().find('.ttxt').html()+'</div></div>');
		$(this).parent().parent().remove();
		$('#llist'+suffix).append(html.join(''));
	})
}
/**商品**/
function loadGoods(suffix){
	var params = FI.getParams('.ipt'+suffix);
	params.key = params['key'+suffix];
	params.goods_cat_id = FI.ITGetGoodsCatVal('pgoodsCats1'+suffix);
	if(params.goods_cat_id==''){
		FI.msg('请选择一个商品分类',{icon:2});
		return;
	}
	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/recommends/searchGoods'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = FI.toAdminJson(data);
    	if(json.status=='1'){
    		if(!json.data)return;
    		json = json.data;
    		$("#llist"+suffix).empty();
    		var ids = $('#ids'+suffix).val().split(',');
			var data,html=[];
			for(var i=0;i<json.length;i++){
				data = json[i]; 
				if($.inArray(data.goods_id.toString(),ids)==-1){
					html.push('<div class="trow"><div class="tck"><input type="checkbox" name="lchk'+suffix+'" class="lchk'+suffix+'" value="'+data.goods_id+'"></div>');
					html.push('<div class="ttxt">【'+data.shop_name+'】'+data.goods_name+'</div></div>');
				}
			}
			$("#llist"+suffix).html(html.join(''));
    	}else{
    		FI.msg(json.msg,{icon:2});
    	}
    });
}
function listQueryByGoods(suffix){
	suffix = (typeof(suffix)=='object')?'_2':suffix;
	$('#rlist'+suffix).empty();
	$('#ids'+suffix).val('');
	var params = {};
	params.data_type = $('#data_type'+suffix).val();
	params.goods_cat_id = FI.ITGetGoodsCatVal('pgoodsCats2'+suffix);
	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/recommends/listQueryByGoods'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = FI.toAdminJson(data);
    	if(json.status=='1'){
    		if(json.data && json.data.length){
    		    json = json.data;
				var data,html=[],ids = [];
				for(var i=0;i<json.length;i++){
					data = json[i]; 
					ids.push(data.data_id);
					html.push('<div class="trow"><div class="tck"><input type="checkbox" name="rchk'+suffix+'" class="rchk'+suffix+'" value="'+data.data_id+'"></div>');
					html.push('<div class="ttxt">【'+data.shop_name+'】'+data.goods_name+'</div>');
					html.push('<div class="top"><input type="text" class="s-sort s-ipt'+suffix+'" value="'+data.data_sort+'" v="'+data.data_id+'"></div></div>');
				}
				$('#ids'+suffix).val(ids.join(','));
				$("#rlist"+suffix).html(html.join(''));
    		}
    		if(FI.ITGetGoodsCatVal('pgoodsCats1'+suffix)>0)loadGoods(suffix);
    	}
    });
}
function editGoods(suffix){
	var params = {},ids = [];
	$('input[name="rchk'+suffix+'"]').each(function(){
		ids.push($(this).val());
	});
	if(ids.length==0){
		FI.msg('请选择要推荐的商品');
		return;
	}
	$('.s-ipt'+suffix).each(function(){
		params['ipt'+$(this).attr('v')] = $(this).val();
	})
	params.ids = ids.join(',');
	params.data_type = $('#data_type'+suffix).val();
	params.goods_cat_id = FI.ITGetGoodsCatVal('pgoodsCats2'+suffix);
	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/recommends/editGoods'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = FI.toAdminJson(data);
    	if(json.status=='1'){
    		FI.msg("保存成功",{icon:1});
    	}else{
    		FI.msg(json.msg,{icon:2});
    	}
    });
}

/***店铺**/
function loadShops(suffix){
	var params = FI.getParams('.ipt'+suffix);
	params.key = params['key'+suffix];
	params.goods_cat_id = FI.ITGetGoodsCatVal('pgoodsCats1'+suffix);
	if(params.goods_cat_id==''){
		FI.msg('请选择一个经营范围',{icon:2});
		return;
	}
	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/recommends/searchShops'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = FI.toAdminJson(data);
    	if(json.status=='1'){
    		if(!json.data)return;
    		json = json.data;
    		$("#llist"+suffix).empty();
    		var ids = $('#ids'+suffix).val().split(',');
			var data,html=[];
			for(var i=0;i<json.length;i++){
				data = json[i]; 
				if($.inArray(data.shop_id.toString(),ids)==-1){
					html.push('<div class="trow"><div class="tck"><input type="checkbox" name="lchk'+suffix+'" class="lchk'+suffix+'" value="'+data.shop_id+'"></div>');
					html.push('<div class="ttxt">【'+data.shop_sn+'】'+data.shop_name+'</div></div>');
				}
			}
			$("#llist"+suffix).html(html.join(''));
    	}else{
    		FI.msg(json.msg,{icon:2});
    	}
    });
}
function listQueryByShops(suffix){
	suffix = (typeof(suffix)=='object')?'_2':suffix;
	$('#rlist'+suffix).empty();
	$('#ids'+suffix).val('');
	var params = {};
	params.data_type = $('#data_type'+suffix).val();
	params.goods_cat_id = FI.ITGetGoodsCatVal('pgoodsCats2'+suffix);
	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/recommends/listQueryByShops'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = FI.toAdminJson(data);
    	if(json.status=='1'){
    		if(json.data && json.data.length){
    		    json = json.data;
				var data,html=[],ids = [];
				for(var i=0;i<json.length;i++){
					data = json[i]; 
					ids.push(data.data_id);
					html.push('<div class="trow"><div class="tck"><input type="checkbox" name="rchk'+suffix+'" class="rchk'+suffix+'" value="'+data.data_id+'"></div>');
					html.push('<div class="ttxt">【'+data.shop_sn+'】'+data.shop_name+'</div>');
					html.push('<div class="top"><input type="text" class="s-sort s-ipt'+suffix+'" value="'+data.data_sort+'" v="'+data.data_id+'"></div></div>');
				}
				$('#ids'+suffix).val(ids.join(','));
				$("#rlist"+suffix).html(html.join(''));
    		}
    		if(FI.ITGetGoodsCatVal('pgoodsCats1'+suffix)>0)loadShops(suffix);
    	}
    });
}

function editShops(suffix){
	var params = {},ids = [];
	$('input[name="rchk'+suffix+'"]').each(function(){
		ids.push($(this).val());
	});
	if(ids.length==0){
		FI.msg('请选择要推荐的店铺');
		return;
	}
	$('.s-ipt'+suffix).each(function(){
		params['ipt'+$(this).attr('v')] = $(this).val();
	})
	params.ids = ids.join(',');
	params.data_type = $('#data_type'+suffix).val();
	params.goods_cat_id = FI.ITGetGoodsCatVal('pgoodsCats2'+suffix);
	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/recommends/editShops'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = FI.toAdminJson(data);
    	if(json.status=='1'){
    		FI.msg("保存成功",{icon:1});
    	}else{
    		FI.msg(json.msg,{icon:2});
    	}
    });
}
/**品牌**/
function loadBrands(suffix){
	var params = FI.getParams('.ipt'+suffix);
	params.key = params['key'+suffix];
	params.goods_cat_id = FI.ITGetGoodsCatVal('pgoodsCats1'+suffix);
	if(params.goods_cat_id==''){
		FI.msg('请选择一个商品分类',{icon:2});
		return;
	}
	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/recommends/searchBrands'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = FI.toAdminJson(data);
    	if(json.status=='1'){
    		if(!json.data)return;
    		json = json.data;
    		$("#llist"+suffix).empty();
    		var ids = $('#ids'+suffix).val().split(',');
			var data,html=[];
			for(var i=0;i<json.length;i++){
				data = json[i]; 
				if($.inArray(data.brand_id.toString(),ids)==-1){
					html.push('<div class="trow"><div class="tck"><input type="checkbox" name="lchk'+suffix+'" class="lchk'+suffix+'" value="'+data.brand_id+'"></div>');
					html.push('<div class="ttxt">'+data.brand_name+'</div></div>');
				}
			}
			$("#llist"+suffix).html(html.join(''));
    	}else{
    		FI.msg(json.msg,{icon:2});
    	}
    });
}
function listQueryByBrands(suffix){
	suffix = (typeof(suffix)=='object')?'_2':suffix;
	$('#rlist'+suffix).empty();
	$('#ids'+suffix).val('');
	var params = {};
	params.data_type = $('#data_type'+suffix).val();
	params.goods_cat_id = FI.ITGetGoodsCatVal('pgoodsCats2'+suffix);
	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/recommends/listQueryByBrands'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = FI.toAdminJson(data);
    	if(json.status=='1'){
    		if(json.data && json.data.length){
    		    json = json.data;
				var data,html=[],ids = [];
				for(var i=0;i<json.length;i++){
					data = json[i]; 
					ids.push(data.data_id);
					html.push('<div class="trow"><div class="tck"><input type="checkbox" name="rchk'+suffix+'" class="rchk'+suffix+'" value="'+data.data_id+'"></div>');
					html.push('<div class="ttxt">'+data.brand_name+'</div>');
					html.push('<div class="top"><input type="text" class="s-sort s-ipt'+suffix+'" value="'+data.data_sort+'" v="'+data.data_id+'"></div></div>');
				}
				$('#ids'+suffix).val(ids.join(','));
				$("#rlist"+suffix).html(html.join(''));
    		}
    		if(FI.ITGetGoodsCatVal('pgoodsCats1'+suffix)>0)loadBrands(suffix);
    	}
    });
}

function editBrands(suffix){
	var params = {},ids = [];
	$('input[name="rchk'+suffix+'"]').each(function(){
		ids.push($(this).val());
	});
	if(ids.length==0){
		FI.msg('请选择要推荐的品牌');
		return;
	}
	$('.s-ipt'+suffix).each(function(){
		params['ipt'+$(this).attr('v')] = $(this).val();
	})
	params.ids = ids.join(',');
	params.data_type = $('#data_type'+suffix).val();
	params.goods_cat_id = FI.ITGetGoodsCatVal('pgoodsCats2'+suffix);
	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/recommends/editBrands'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = FI.toAdminJson(data);
    	if(json.status=='1'){
    		FI.msg("保存成功",{icon:1});
    	}else{
    		FI.msg(json.msg,{icon:2});
    	}
    });
}