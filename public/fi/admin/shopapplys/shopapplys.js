var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/shopapplys/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '申请人', name: 'user_name',isSort: false,render: function (rowdata, rowindex, value){
	            return rowdata['linkman']+FI.blank(rowdata['login_name']);
	        }},
	        { display: '联系电话', name: 'phone_no',Sort: false},
	        { display: '申请说明', name: 'apply_desc',Sort: false},
	        { display: '申请时间', name: 'create_time',Sort: false},
	        { display: '状态', name: 'apply_status',Sort: false,render: function (rowdata, rowindex, value){
	            return (rowdata['apply_status']==1)?"已处理":((rowdata['apply_status']==-1)?"申请失败":"未处理");
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(rowdata['apply_status']==0 && FI.GRANT.DPSQ_04)h += "<a href='javascript:toEdit(" + rowdata['apply_id'] + ")'>处理</a> ";
	            if(FI.GRANT.DPSQ_03)h += "<a href='javascript:toDel(" + rowdata['apply_id'] + ")'>删除</a> ";
	            if(FI.GRANT.DPGL_01 && !rowdata['shop_id'] && rowdata['apply_status']==1)h += "<a href='javascript:toAddShop(" + rowdata['apply_id'] + ")'>开店</a> ";
	            return h;
	        }}
        ]
    });
}
function toEdit(id){
	location.href=FI.U('admin/shopapplys/toHandle','id='+id);
}
function toAddShop(id){
	location.href=FI.U('admin/shops/toAddByApply','id='+id);
}
function toDel(id){
	var box = FI.confirm({content:"您确定要删除该开店申请吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           $.post(FI.U('admin/shopapplys/del'),{id:id},function(data,textStatus){
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
function save(){
	if(!$('input[name="apply_status"]').isValid())return;
	if($('input[name="apply_status"]:checked').val()==-1 && !$('#handle_desc').isValid())return;
	var params = FI.getParams('.ipt');
	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
    $.post(FI.U('admin/shopapplys/handle'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = FI.toAdminJson(data);
    	if(json.status=='1'){
    		FI.msg("操作成功",{icon:1});
    		if(FI.GRANT.DPGL_01 && params.apply_status==1){
    			toAddShop(params.apply_id);
    		}else{
    		    location.href=FI.U('admin/shopapplys/index');
    		}
    	}else{
    		FI.msg(json.msg,{icon:2});
    	}
    });
}
