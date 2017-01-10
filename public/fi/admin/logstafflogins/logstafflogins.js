var grid;
$(function(){
	$("#startDate").ligerDateEditor();
	$("#endDate").ligerDateEditor();
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/logstafflogins/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '职员', name: 'staff_name'},
	        { display: '登录时间', name: 'login_time'},
	        { display: '登录IP', name: 'login_ip'}
        ]
    });
})
function loadGrid(){
	grid.set('url',FI.U('admin/logstafflogins/pageQuery','startDate='+$('#startDate').val()+"&endDate="+$('#endDate').val()))
}