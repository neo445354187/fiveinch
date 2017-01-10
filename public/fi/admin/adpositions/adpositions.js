var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/Adpositions/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '位置名称', name: 'position_name', isSort: false ,width:'50%',heightAlign:'left',align:'left'},
	        { display: '宽度', name: 'position_width', isSort: false},
	        { display: '高度', name: 'position_height', isSort: false},
	        { display: '位置类型', name: 'position_type', isSort: false,render:function(rowdata, rowindex, value){
	        	return (rowdata['position_type']==1)?'PC版':'微信版';
	        }},
          { display: '位置代码', name: 'position_code', isSort: false},
	        { display: '排序号', name: 'ap_sort', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	        	var h = "";
	            if(FI.GRANT.GGWZ_02)h += "<a href='"+FI.U('admin/AdPositions/toEdit','id='+rowdata['position_id'])+"'>修改</a> ";
	            if(FI.GRANT.GGWZ_03)h += "<a href='javascript:toDel(" + rowdata['position_id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}
function toDel(id){
	var box = FI.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(FI.U('admin/AdPositions/del'),{id:id},function(data,textStatus){
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



function editInit(){
	 /* 表单验证 */
    $('#adPositionsForm').validator({
            fields: {
                position_type: {
                  rule:"required",
                  msg:{required:"请选择位置类型"},
                  tip:"请选择位置类型",
                  ok:"",
                },
                position_name: {
                  rule:"required;",
                  msg:{required:"请输入位置名称"},
                  tip:"请输入位置名称",
                  ok:"",
                },
                position_code: {
                    rule:"required;",
                    msg:{required:"请输入位置代码"},
                    tip:"请输入位置代码",
                    ok:"",
                  },
                position_width: {
                  rule:"required;",
                  msg:{required:"请输入建议宽度"},
                  ok:"",
                },
                position_height: {
                  rule:"required",
                  msg:{required:"请输入建议高度"},
                  ok:"",
                }
            },
          valid: function(form){
            var params = FI.getParams('.ipt');
            var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(FI.U('admin/Adpositions/'+((params.position_id==0)?"add":"edit")),params,function(data,textStatus){
              layer.close(loading);
              var json = FI.toAdminJson(data);
              if(json.status=='1'){
                  FI.msg("操作成功",{icon:1});
                  location.href=FI.U('Admin/Adpositions/index');
              }else{
                    FI.msg(json.msg,{icon:2});
              }
            });
      }
    });
}