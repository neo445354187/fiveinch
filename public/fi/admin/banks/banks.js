var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/banks/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '银行名称', name: 'bank_name', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(FI.GRANT.YHGL_02)h += "<a href='javascript:getForEdit(" + rowdata['bank_id'] + ")'>修改</a> ";
	            if(FI.GRANT.YHGL_03)h += "<a href='javascript:toDel(" + rowdata['bank_id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}
function toDel(id){
	var box = FI.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(FI.U('admin/banks/del'),{id:id},function(data,textStatus){
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
     $.post(FI.U('admin/banks/get'),{id:id},function(data,textStatus){
           layer.close(loading);
           var json = FI.toAdminJson(data);
           if(json.bank_id){
           		FI.setValues(json);
           		toEdit(json.bank_id);
           }else{
           		FI.msg(json.msg,{icon:2});
           }
    });
}

function toEdit(id){
	var title =(id==0)?"新增":"编辑";
	var box = FI.open({title:title,type:1,content:$('#bankBox'),area: ['450px', '160px'],btn:['确定','取消'],yes:function(){
		$('#bankForm').submit();
	}});
	$('#bankForm').validator({
        fields: {
            bank_name: {
            	rule:"required;",
            	msg:{required:"银行名称不能为空"},
            	tip:"请输入银行名称",
            	ok:"",
            },
           
        },
       valid: function(form){
		        var params = FI.getParams('.ipt');
	                params.bank_id = id;
	                var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           		$.post(FI.U('admin/banks/'+((id==0)?"add":"edit")),params,function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = FI.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	FI.msg("操作成功",{icon:1});
	           			    	$('#bankForm')[0].reset();
	           			    	layer.close(box);
	           		            grid.reload();
	           			  }else{
	           			        FI.msg(json.msg,{icon:2});
	           			  }
	           		});

    	}

  });

}