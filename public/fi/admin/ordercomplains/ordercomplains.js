var grid;
$(function(){
h = FI.pageHeight();
$('.l-tab-content').height(h-25);
$('.l-tab-content-item').height(h-25);
$('.l-tab-content-item').css('overflow-y','auto');
});
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/ordercomplains/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '投诉人', name: 'user_name',isSort: false,render:function(rowdata, rowindex, value){
	        	return FI.blank(rowdata['user_name'],rowdata['login_name']);
	        }},
	        { display: '投诉订单号', name: 'order_no',isSort: false},
	        { display: '被投诉人', name: 'shop_name',isSort: false},
	        { display: '投诉类型', name: 'complain_type',isSort: false,render:function(rowdata, rowindex, value){
	        	var html='';
	        	if(value==1)
	        		html = '承诺的没有做到';
	        	else if(value==2)
	        		html = '未按约定时间发货';
	        	else if(value==3)
	        		html = '未按成交价格进行交易';
	        	else if(value==4)
	        		html = '恶意骚扰';
	        	return html;
	        }},
	        { display: '投诉时间', name: 'complain_time',isSort: false},
	        { display: '状态', name: 'complain_status',isSort: false,render:function(rowdata, rowindex, value){
	        	var html='';
	        	if(value==0)
	        		html = '新投诉';
	        	else if(value==1)
	        		html = '转给应诉人';
	        	else if(value==2)
	        		html = '应诉人回应';
	        	else if(value==3)
	        		html = '等待仲裁';
	        	else if(value==4)
	        		html = '已仲裁';
	        	return html;
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            h += "<a href='javascript:toView(" + rowdata['complain_id'] + ")'>查看</a> ";
	            if(rowdata['complain_status']!=4)
	            h += "<a href='javascript:toHandle(" + rowdata['complain_id'] + ")'>处理</a> ";
	            return h;
	        }}
            
        ]
    });
}
function toView(id){
	location.href=FI.U('admin/orderComplains/view','cid='+id);
}
function toHandle(id){
	location.href=FI.U('admin/orderComplains/toHandle','cid='+id);
}
function loadGrid(){
	var p = FI.arrayParams('.j-ipt');
	grid.set('url',FI.U('admin/orderComplains/pageQuery',p.join('&')));
}


function deliverNext(id){
     FI.confirm({content:'您确定要转交给应诉人应诉吗?',yes:function(){
       $.post(FI.U('Admin/Ordercomplains/deliverRespond'),{id:id},function(data,textStatus){
          var json = FI.toAdminJson(data);
          if(json.status=='1'){
        	  FI.msg('投诉已移交应诉人',{icon:1},function(){
        		  location.reload();
        	  });
          }else{
            FI.msg(json.msg,{icon:2});
          }
        });
     }});
}

function finalHandle(id){
   var params = {};
   params.cid = id;
   

   params.order_status = $.trim($('#order_status').val());
   params.final_result = $.trim($('#final_result').val());
   if(params.final_result==''){
     FI.msg('请输入仲裁结果!',{icon:2});
     return;
   }

   var c = FI.confirm({title:'信息提示',content:'您确定仲裁该订单投诉记录吗?',yes:function(){
     layer.close(c);
     $.post(FI.U('Admin/OrderComplains/finalHandle'),params,function(data,textStatus){
        var json = FI.toAdminJson(data);
        if(json.status=='1'){
          FI.msg(json.msg,{icon:1});
          location.reload();
        }else{
          FI.msg(json.msg,{icon:2});
        }
      });
   }});
}

  
