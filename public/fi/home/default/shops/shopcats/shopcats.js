function addCat(obj,p,catNo){
	var html = new Array();
	if(typeof(obj)=="number"){
		$("#cat_list_tab").append($("#cat_p_tr").html());
	}else{
		var className = (p==0)?"tr_c_new":"tr_"+catNo+" tr_0";
		var gettpl = $("#cat_c_tr").html();
		laytpl(gettpl).render({"className":className,"p":p}, function(html){
			$(obj).parent().parent().parent().append(html);
		});
	}
	$('.fi-shop-but').show();
}

function delCatObj(obj,vk){
	if(vk==1){
		$(obj).parent().parent().parent().remove();
	}else{
		$(obj).parent().parent().remove();
	}
	if($(".tr_0").size()==0 && $(".tbody_new").size()==0)$('.fi-shop-but').hide();
}

function treeCatOpen(obj,id){
    if( $(obj).attr('class').indexOf('active') > -1 ){
    	$(obj).removeClass('active');
        $(obj).html('<img class="fi-lfloat" style="margin-top:-3px;" src="'+FI.conf.ROOT+'/fi/home/default/img/seller_icon_sq.png">');
        $('.tree_'+id).hide();
    }else{
    	$(obj).addClass('active');
        $(obj).html('<img class="fi-lfloat" style="margin-top:-3px;" src="'+FI.conf.ROOT+'/fi/home/default/img/seller_icon_zk.png">');
        $('.tree_'+id).show();
    }
}

function delCat(id){
	var box = FI.confirm({content:"您确定要删除该商品分类吗？",yes:function(){
		var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		$.post(FI.U('home/shopcats/del'),{id:id},function(data,textStatus){
			layer.close(loading);
			var json = FI.toJson(data);
			if(json.status=='1'){
				FI.msg("操作成功",{icon:1});
				layer.close(box);
				location.reload();
			}else{
				FI.msg(json.msg,{icon:2});
			}
		});
	}});
}


function batchSaveCats(){
	var params = {};
	var fristNo = 0;
	var secondNo = 0;
	$(".tbody_new").each(function(){
		secondNo = 0;
		var pobj = $(this).find(".tr_new");
		params['cat_name_'+fristNo] = $.trim(pobj.find(".catname").val());
		if(params['cat_name_'+fristNo]==''){
			FI.msg('请输入商品分类名称!', {icon: 5});
			return;
		}
		params['cat_sort_'+fristNo] = pobj.find(".catsort").val();
		params['catShow_'+fristNo] = pobj.find(".catshow").prop("checked")?1:0
		$(this).find(".tr_c_new").each(function(){
			params['cat_id_'+fristNo+'_'+secondNo] = fristNo;
			params['cat_name_'+fristNo+'_'+secondNo] = $.trim($(this).find(".catname").val());
			if(params['cat_name_'+fristNo+'_'+secondNo]==''){
				FI.msg('请输入商品分类名称!', {icon: 5});
				return;
			}
			params['cat_sort_'+fristNo+'_'+secondNo] = $(this).find(".catsort").val();
			params['catShow_'+fristNo+'_'+secondNo] = $(this).find(".catshow").prop("checked")?1:0
			params['catSecondNo_'+fristNo] = ++secondNo;		
		});
		params['fristNo'] = ++fristNo;
	});
	var otherNo = 0;
	$(".tr_0").each(function(){
		params['cat_id_o_'+otherNo] = $(this).attr('cat_id');
		params['cat_name_o_'+otherNo] = $.trim($(this).find(".catname").val());
		if(params['cat_name_o_'+otherNo]==''){
			FI.msg('请输入商品分类名称!', {icon: 5});
			return;
		}
		params['cat_sort_o_'+otherNo] = $(this).find(".catsort").val();
		params['catShow_o_'+otherNo] = $(this).find(".catshow").prop("checked")?1:0;
		params['otherNo'] = ++otherNo;
	});
	$.post(FI.U('home/shopcats/batchSaveCats'),params,function(data,textStatus){
		var json = FI.toJson(data);
		if(json.status==1){
			FI.msg('新增成功!', {icon: 1,time:500},function(){
				location.reload();
			}); 
		}else{
			FI.msg('新增失败!', {icon: 5}); 
		}
	});
}


function editCatName(obj){
	$.post(FI.U('home/shopcats/editName'),{"id":$(obj).attr('data_id'),"cat_name":obj.value},function(data,textStatus){
		var json = FI.toJson(data);
		if(json.status=='1'){
			FI.msg('操作成功!',{icon: 1,time:500});
		}else{
			FI.msg('操作失败!', {icon: 5});
		}
	});
}
function editCatSort(obj){
	$.post(FI.U('home/shopcats/editSort'),{"id":$(obj).attr('data_id'),"cat_sort":obj.value},function(data,textStatus){
		var json = FI.toJson(data);
		if(json.status=='1'){
			FI.msg('操作成功!',{icon: 1,time:500});
		}else{
			FI.msg('操作失败!', {icon: 5});
		}
	});
}

function changeCatStatus(is_show,id,pid){
	var params = {};
		params.id = id;
		params.is_show = is_show;
		params.pid = pid;
	$.post(FI.U('home/shopcats/changeCatStatus'),params,function(data,textStatus){
		location.reload();  
	});
	
}