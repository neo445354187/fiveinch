var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/friend_links/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '网站名称', name: 'friend_link_name', isSort: false},
	        { display: '网址', name: 'friend_link_url', isSort: false},
	        { display: '图标', name: 'friend_link_ico', isSort: false,render:function(rowdata, rowindex, value){
	        	return '<img src="'+FI.conf.ROOT+'/'+rowdata['friend_link_ico']+'" height="28px" />';
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(FI.GRANT.YQGL_02)h += "<a href='javascript:getForEdit(" + rowdata['friend_link_id'] + ")'>修改</a> ";
	            if(FI.GRANT.YQGL_03)h += "<a href='javascript:toDel(" + rowdata['friend_link_id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });

}
function toDel(id){
	var box = FI.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(FI.U('admin/friend_links/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = FI.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	FI.msg("操作成功",{icon:1});
	           			    	layer.close(box);
	           		            grid.reload();
	           			  }else{
	           			    	FI.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}

function getForEdit(id){
	 var loading = FI.msg('正在获取数据，请稍后...', {icon: 16,time:60000});
     $.post(FI.U('admin/friend_links/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = FI.toAdminJson(data);
           if(json.friend_link_id){
           		FI.setValues(json);
           		//显示原来的图片
           		if(json.friend_link_ico!=''){
           			$('#preview').html('<img src="'+FI.conf.ROOT+'/'+json.friend_link_ico+'" height="70px" />');
           		}else{
           			$('#preview').empty();
           		}
           		toEdit(json.friend_link_id);
           }else{
           		FI.msg(json.msg,{icon:2});
           }
    });
}

function toEdit(id){
	var title =(id==0)?"新增":"编辑";
	var box = FI.open({title:title,type:1,content:$('#friendlinkBox'),area: ['450px', '350px'],btn: ['确定','取消'],yes:function(){
			$('#friendlinkForm').submit();
	},cancel:function(){
		//重置表单
		$('#friendlinkForm')[0].reset();
		//清空预览图
		$('#preview').html('');
		//清空图片隐藏域
		$('#friend_link_ico').val('');
	},end:function(){
		//重置表单
		$('#friendlinkForm')[0].reset();
		//清空预览图
		$('#preview').html('');
		//清空图片隐藏域
		$('#friend_link_ico').val('');

	}});
	$('#friendlinkForm').validator({
        fields: {
            friend_link_name: {
            	rule:"required;",
            	msg:{required:"网站名称不能为空"},
            	tip:"请输入网站名称",
            	ok:"",
            },
            friend_link_url: {
	            rule: "required;",
	            msg: {required: "网址不能为空"},
	            tip: "请输入网址",
	            ok: "",
        	}
        },
       valid: function(form){
		        var params = FI.getParams('.ipt');
		        params.friend_link_id = id;
		        var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		   		$.post(FI.U('admin/friend_links/'+((id==0)?"add":"edit")),params,function(data,textStatus){
		   			  layer.close(loading);
		   			  var json = FI.toAdminJson(data);
		   			  if(json.status=='1'){
		   			    	FI.msg("操作成功",{icon:1});
		   			    	$('#friendlinkForm')[0].reset();
		   			    	//清空预览图
		   			    	$('#preview').html('');
		   			    	//清空图片隐藏域
		   			    	$('#friend_link_ico').val('');
		   			    	layer.close(box);
		   		            grid.reload();
		   			  }else{
		   			        FI.msg(json.msg,{icon:2});
		   			  }
		   		});

    	}

  });
}



$(function(){
//文件上传
FI.upload({
    pick:'#ad_filePicker',
    formData: {dir:'friendlinks'},
    accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
    callback:function(f){
      var json = FI.toAdminJson(f);
      if(json.status==1){
	      $('#uploadMsg').empty().hide();
	   	  $('#friend_link_ico').val(json.savePath+json.thumb);
	   	  $('#preview').html('<img src="'+FI.conf.ROOT+'/'+json.savePath+json.thumb+'" height="75" />');
      }else{
      	  FI.msg(json.msg,{icon:2});
      }
  },
  progress:function(rate){
      $('#uploadMsg').show().html('已上传'+rate+"%");
  }
});

});
