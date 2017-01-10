var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/staffs/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '职员账号', name: 'login_name',isSort: false},
	        { display: '职员名称', name: 'staff_name',isSort: false},
	        { display: '职员角色', name: 'role_name',isSort: false},
	        { display: '职员编号', name: 'staff_no',isSort: false},
	        { display: '工作状态', name: 'work_itatus',isSort: false,render: function (rowdata, rowindex, value){
	        	return (value==1)?"在职":"离职";
	        }},
	        { display: '登录时间', name: 'last_time',isSort: false},
	        { display: '登录IP', name: 'last_ip',isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(FI.GRANT.ZYGL_02)h += "<a href='javascript:toEditPass(" + rowdata['staff_id'] + ")'>修改密码</a> ";
	            if(FI.GRANT.ZYGL_02)h += "<a href='javascript:toEdit(" + rowdata['staff_id'] + ")'>修改</a> ";
	            if(FI.GRANT.ZYGL_03)h += "<a href='javascript:toDel(" + rowdata['staff_id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}
function loadGrid(){
	grid.set('url',FI.U('admin/staffs/pageQuery','key='+$('#key').val()));
}
function toEdit(id){
	location.href=FI.U('admin/staffs/'+((id==0)?'toAdd':'toEdit'),'id='+id);
}
function toDel(id){
	var box = FI.confirm({content:"您确定要删除该职员吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           $.post(FI.U('admin/staffs/del'),{id:id},function(data,textStatus){
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
    $.post(FI.U('admin/staffs/checkLoginKey'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = FI.toAdminJson(data);
    	if(json.status!='1'){
    		FI.msg(json.msg,{icon:2});
    		obj.value = '';
    	}
    });
}
function save(){
	var params = FI.getParams('.ipt');
	if(params.staff_id==0){
		if(!$('#login_name').isValid())return;
		if(!$('#login_password').isValid())return;
	}
	if(!$('#staff_name').isValid())return;
	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/staffs/'+((params.staff_id==0)?"add":"edit")),params,function(data,textStatus){
    	layer.close(loading);
    	var json = FI.toAdminJson(data);
    	if(json.status=='1'){
    		FI.msg("操作成功",{icon:1});
    		location.href=FI.U('admin/staffs/index');
    	}else{
    		FI.msg(json.msg,{icon:2});
    	}
    });
}
function toEditPass(id){
	var w = FI.open({type: 1,title:"修改密码",shade: [0.6, '#000'],border: [0],content:$('#editPassBox'),area: ['450px', '200px'],
	    btn: ['确定', '取消'],yes: function(index, layero){
	    	$('#editPassFrom').isValid(function(v){
	    		if(v){
		        	var params = FI.getParams('.ipt');
		        	params.staff_id = id;
		        	var ll = FI.msg('数据处理中，请稍候...');
				    $.post(FI.U('admin/Staffs/editPass'),params,function(data){
				    	layer.close(ll);
				    	var json = FI.toAdminJson(data);
						if(json.status==1){
							FI.msg(json.msg, {icon: 1});
							layer.close(w);
						}else{
							FI.msg(json.msg, {icon: 2});
						}
				   });
	    		}})
        }
	});
}
