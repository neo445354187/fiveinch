$(function(){
	areasByList();
});
//运费列表
function areasByList(){
    $.post(FI.U('home/shopfreights/listProvince'),'',function(data){
        var json = FI.toJson(data);
        var gettpl = document.getElementById('list').innerHTML;
        laytpl(gettpl).render(json, function(html){
            $('#list-info').html(html);
        });
    });
}

function treeOpen(obj,id){
    if( $(obj).attr('class').indexOf('active') > -1 ){
    	$(obj).removeClass('active');
        $(obj).html('<img class="fi-lfloat" style="margin-top:-3px;" src="'+FI.conf.ROOT+'/fi/home/default/img/seller_icon_zk.png">');
        $('.text_'+id).show();
        $('.tree_'+id).show();
    }else{
    	$(obj).addClass('active');
        $(obj).html('<img class="fi-lfloat" style="margin-top:-3px;" src="'+FI.conf.ROOT+'/fi/home/default/img/seller_icon_sq.png">');
        $('.text_'+id).hide();
        $('.tree_'+id).hide();
    }
}

function freightOnblur(obj,id,v){
	$postage = $(obj).val();
	if(v == 0){
		$('.possort').val($postage);
	}else{
		$('.price_'+id).val($postage);
	}
}

function freightSubmit(){
    var params = FI.getParams('.ipt');
    var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('home/shopfreights/edit'),params,function(data,textStatus){
      layer.close(loading);
      var json = FI.toJson(data);
      if(json.status=='1'){
          FI.msg(json.msg,{icon:1});
	        setTimeout(function(){ 
	        	location.href=FI.U('home/shopfreights/index');
	  	    },2000);
      }else{
            FI.msg(json.msg,{icon:2});
      }
    });
}