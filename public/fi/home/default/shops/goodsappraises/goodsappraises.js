/**获取本店分类**/
function getShopsCats(objId,pVal,objVal){
	$('#'+objId).empty();
	$.post(FI.U('home/shopcats/listQuery'),{parent_id:pVal},function(data,textStatus){
	     var json = FI.toJson(data);
	     var html = [],cat;
	     html.push("<option value='' >-请选择-</option>");
	     if(json.status==1 && json.list){
	    	 json = json.list;
			 for(var i=0;i<json.length;i++){
			     cat = json[i];
			     html.push("<option value='"+cat.cat_id+"' "+((objVal==cat.cat_id)?"selected":"")+">"+cat.cat_name+"</option>");
			 }
	     }
	     $('#'+objId).html(html.join(''));
	});
}
function getCat(val){
  if(val==0){
     $('#cat2').html("<option value='' >-请选择-</option>");
     return;
  }
  $.post(FI.U('home/shopcats/listQuery'),{parent_id:val},function(data,textStatus){
       var json = FI.toJson(data);
       var html = [],cat;
       html.push("<option value='' >-请选择-</option>");
       if(json.status==1 && json.list){
         json = json.list;
       for(var i=0;i<json.length;i++){
           cat = json[i];
           html.push("<option value='"+cat.cat_id+"'>"+cat.cat_name+"</option>");
        }
       }
       $('#cat2').html(html.join(''));
  });
}
function showImg(id){
  layer.photos({
      photos: '#img-file-'+id
    });
}
function queryByPage(p){
	$('#list').html('<img src="'+FI.conf.ROOT+'/fi/home/default/img/loading.gif">正在加载数据...');
	var params = {};
	params = FI.getParams('.s-query');
	params.key = $.trim($('#key').val());
	params.p = p;
	$.post(FI.U('home/goodsappraises/queryByPage'),params,function(data,textStatus){
	    var json = FI.toJson(data);
	    if(!json.data){
	    	$('#list').html(' ');
	    }
	    if(json.status==1 && json.data){
	       	var gettpl = document.getElementById('tblist').innerHTML;
	       	laytpl(gettpl).render(json.data, function(html){
	       		$('#list').html(html);
	        	for(var g=0;g<=json.data.length;g++){
	       			showImg(g);
	       		}
	       		$('.gImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+window.conf.GOODS_LOGO});
	       	});
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

function reply(t,id){
 var params = {};
 if($('#reply-'+id).val()==''){
    FI.msg('回复内容不能为空',{icon:2});
    return false;
 }
 params.reply = $('#reply-'+id).val();
 params.id=id;
 $.post(FI.U('home/goodsappraises/shop_reply'),params,function(data){
    var json = FI.toJson(data);
    if(json.status==1){
      var today = new Date();
          today = today.toLocaleDateString();
      var html = '<p class="reply-content">'+params.reply+'【'+today+'】</p>'
      $(t).parent().html(html);
    }
 });
}
