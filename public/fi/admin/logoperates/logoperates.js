var grid;
$(function(){
	$("#startDate").ligerDateEditor();
	$("#endDate").ligerDateEditor();
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/logoperates/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '职员', name: 'staff_name',isSort: false},
	        { display: '操作功能', name: 'operate_desc',isSort: false,render: function (rowdata, rowindex, value){
	        	return rowdata['menu_name']+"-"+rowdata['operate_desc'];
	        }},
	        { display: '访问路径', name: 'operate_url',isSort: false},
	        { display: '操作IP', name: 'operate_ip',isSort: false},
	        { display: '操作时间', name: 'operate_time',isSort: false},
	        { display: '传递参数', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	        	return '<a href="javascript:toView('+rowdata['operate_id']+')">查看</a>';
	        }}
        ]
    });
})
function loadGrid(){
	grid.set('url',FI.U('admin/logoperates/pageQuery','startDate='+$('#startDate').val()+"&endDate="+$('#endDate').val()))
}
function toView(id){
	 var loading = FI.msg('正在获取数据，请稍后...', {icon: 16,time:60000});
	 $.post(FI.U('admin/logoperates/get'),{id:id},function(data,textStatus){
	       layer.close(loading);
	       var json = FI.toAdminJson(data);
	       if(json.status==1){
	    	   $('#content').html(json.data.content);
	    	   var box = FI.open({ title:"传递参数",type: 1,area: ['500px', '350px'],
		                content:$('#viewBox'),
		                btn:['关闭'],
		                yes: function(index, layero){
		                	layer.close(box);
		                }
	    	   });
	       }else{
	           FI.msg(json.msg,{icon:2});
	       }
	 });
}