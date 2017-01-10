var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/shops/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '店铺编号', name: 'shop_sn',isSort: false},
	        { display: '店铺名称', name: 'shop_name',isSort: false},
	        { display: '店主姓名', name: 'shop_keeper',isSort: false},
	        { display: '店主联系电话', name: 'telephone',isSort: false},
	        { display: '店主店铺地址', name: 'shop_address',isSort: false},
	        { display: '所属公司', name: 'shop_company',isSort: false},
	        { display: '营业状态', name: 'shop_ative',isSort: false,render: function (rowdata, rowindex, value){
	        	return (rowdata['shop_ative']==1)?"营业中":"休息中";
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(FI.GRANT.DPGL_02)h += "<a href='javascript:toEdit(" + rowdata['shop_id'] + ")'>修改</a> ";
	            if(FI.GRANT.DPGL_03)h += "<a href='javascript:toDel(" + rowdata['shop_id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}
function initStopGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/shops/pageStopQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
            { display: '店铺编号', name: 'shop_sn',isSort: false},
	        { display: '店铺名称', name: 'shop_name',isSort: false},
	        { display: '店主姓名', name: 'shop_keeper',isSort: false},
	        { display: '店主联系电话', name: 'telephone',isSort: false},
	        { display: '店主店铺地址', name: 'shop_address',isSort: false},
	        { display: '所属公司', name: 'shop_company',isSort: false},
	        { display: '营业状态', name: 'shop_ative',isSort: false,render: function (rowdata, rowindex, value){
	        	return (rowdata['shop_ative']==1)?"营业中":"休息中";
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            h += "<a href='javascript:toEdit(" + rowdata['shop_id'] + ")'>修改</a> ";
	            h += "<a href='javascript:toDel(" + rowdata['shop_id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}
function initEdit(opts){
	FI.upload({
	  	  pick:'#shop_imgPicker',
	  	  formData: {dir:'shops'},
	  	  accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
	  	  callback:function(f){
	  		  var json = FI.toAdminJson(f);
	  		  if(json.status==1){
	  			$('#uploadMsg').empty().hide();
	            $('#preview').attr('src',FI.conf.ROOT+"/"+json.savePath+json.thumb);
	            $('#shop_img').val(json.savePath+json.name);
	            $('#editFrom').validator('hideMsg', '#shop_img');
	  		  }
		  },
		  progress:function(rate){
		      $('#uploadMsg').show().html('已上传'+rate+"%");
		  }
	});
	initTime('#service_start_time',opts.service_start_time);
	initTime('#service_end_time',opts.service_end_time);
	if($('#shop_id').val()>0){
		var area_id_path = opts.area_id_path.split("_");
    	$('#area_0').val(area_id_path[0]);
    	var aopts = {id:'area_0',val:area_id_path[0],childIds:area_id_path,className:'j-areas',isRequire:true}
		FI.ITSetAreas(aopts);
	}
	
}
function toEdit(id){
	location.href=FI.U('admin/shops/toEdit','id='+id);
}
function toDel(id){
	var box = FI.confirm({content:"您确定要删除该店铺吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           $.post(FI.U('admin/shops/del'),{id:id},function(data,textStatus){
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
function checkLoginKey(obj){
	if($.trim(obj.value)=='')return;
	var params = {key:obj.value,user_id:0};
	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/users/checkLoginKey'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = FI.toAdminJson(data);
    	if(json.status!='1'){
    		FI.msg(json.msg,{icon:2});
    		obj.value = '';
    	}
    });
}
function save(){
	$('#editFrom').isValid(function(v){
		if(v){
			var params = FI.getParams('.ipt');
			params.area_id = FI.ITGetAreaVal('j-areas');
			var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		    $.post(FI.U('admin/shops/'+((params.shop_id==0)?"add":"edit")),params,function(data,textStatus){
		    	layer.close(loading);
		    	var json = FI.toAdminJson(data);
		    	if(json.status=='1'){
		    		FI.msg("操作成功",{icon:1});
		    		location.href=FI.U('admin/shops/index');
		    	}else{
		    		FI.msg(json.msg,{icon:2});
		    	}
		    });
		}
	});
}
function initTime($id,val){
	var html = [],t0,t1;
	var str = val.split(':');
	for(var i=0;i<24;i++){
		t0 = (val.indexOf(':00')>-1 && (parseInt(str[0],10)==i))?'selected':'';
		t1 = (val.indexOf(':30')>-1 && (parseInt(str[0],10)==i))?'selected':'';
		html.push('<option value="'+i+':00" '+t0+'>'+i+':00</option>');
		html.push('<option value="'+i+':30" '+t1+'>'+i+':30</option>');
	}
	$($id).append(html.join(''));
}
