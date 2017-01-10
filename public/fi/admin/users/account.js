var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/users/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '账号', name: 'login_name', isSort: false},
	        { display: '用户名', name: 'user_name', isSort: false},
	        { display: '手机号码', name: 'user_phone', isSort: false},
	        { display: '电子邮箱', name: 'user_email', isSort: false},
	        { display: '最后登录时间', name: 'last_time', isSort: false},
	        { display: '状态', name: 'user_status', isSort: false, render:function(rowdata, rowindex, value){
	        	return (value==1)?'<span style="cursor:pointer;" onclick="changeUserStatus('+rowdata['user_id']+',0)">启用</span>':'<span style="cursor:pointer;" onclick="changeUserStatus('+rowdata['user_id']+',1)">停用</span>';
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(FI.GRANT.ZHGL_02)h += "<a href='javascript:getForEdit(" + rowdata['user_id'] + ")'>修改</a> ";
	            return h;
	        }}
        ]
    });
}

function getForEdit(id){
	 var loading = FI.msg('正在获取数据，请稍后...', {icon: 16,time:60000});
     $.post(FI.U('admin/users/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = FI.toAdminJson(data);
           //清空密码
           json.login_password = '';
           if(json.user_id){
           		FI.setValues(json);
           		toEdit(json.user_id);
           }else{
           		FI.msg(json.msg,{icon:2});
           }
    });
}

function toEdit(id){
	var box = FI.open({title:'编辑',type:1,content:$('#accountBox'),area: ['450px', '260px'],btn:['确定','取消'],yes:function(){
					$('#accountForm').isValid(function(v){
						if(v){
							var params = FI.getParams('.ipt');
			                if(id>0)
			                	params.user_id = id;
			                var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
			           		$.post(FI.U('admin/users/editAccount'),params,function(data,textStatus){
			           			  layer.close(loading);
			           			  var json = FI.toAdminJson(data);
			           			  if(json.status=='1'){
			           			    	FI.msg("操作成功",{icon:1});
			           			    	$('#accountForm')[0].reset();
			           			    	layer.close(box);
			           		            grid.reload();
			           			  }else{
			           			        FI.msg(json.msg,{icon:2});
			           			  }
			           		});
						}else{
							return false;
						}
					});
		        	
		

	},cancel:function(){$('#accountForm')[0].reset();},end:function(){$('#accountForm')[0].reset();}});

}

function changeUserStatus(id, status){
	if(!FI.GRANT.ZHGL_02)return;
	$.post(FI.U('admin/Users/changeUserStatus'), {'id':id, 'status':status}, function(data, textStatus){
		var json = FI.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	FI.msg("操作成功",{icon:1});
	           		            grid.reload();
	           			  }else{
	           			    	FI.msg(json.msg,{icon:2});
	           			  }
	})
}


function accountQuery(){
          var query = FI.getParams('.query');
			    grid.set('url',FI.U('admin/Users/pageQuery',query));
			}

		