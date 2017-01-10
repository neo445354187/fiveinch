var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/payments/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '名称', name: 'pay_name', isSort: false},
	        { display: '描述', name: 'pay_desc', isSort: false},
	        { display: '状态', name: 'enabled', isSort: false,render: function (rowdata, rowindex, value){
	            return value==1?"是":"否";}},
	        { display: '排序号', name: 'pay_order', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(rowdata['enabled']==1){
		            if(FI.GRANT.ZFGL_02)h += "<a href='"+FI.U('admin/payments/toEdit','id='+rowdata['id']+'&pay_code='+rowdata['pay_code'])+"'>编辑</a> ";
		            if(FI.GRANT.ZFGL_03)h += "<a href='javascript:toDel(" + rowdata['id'] + ")'>卸载</a> "; 
	            }
	            else{
	            	if(FI.GRANT.ZFGL_02)h += "<a href='"+FI.U('admin/payments/toEdit','id='+rowdata['id']+'&pay_code='+rowdata['pay_code'])+"'>安装</a> ";
	            }
	            return h;
	        }}
        ]
    });
}

function toDel(id){
	var box = FI.confirm({content:"您确定卸载吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(FI.U('admin/payments/del'),{id:id},function(data,textStatus){
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


function edit(id){
	//获取所有参数
	var params = FI.getParams('.ipt');
	//接收配置信息并转成JSON
	var configs = JSON.stringify(FI.getParams('.cfg'));
	//保存配置信息
	params.pay_config = configs;
	params.id = id;
	var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	$.post(FI.U('admin/payments/'+((id==0)?"add":"edit")),params,function(data,textStatus){
	  layer.close(loading);
	  var json = FI.toAdminJson(data);
	  if(json.status=='1'){
	      FI.msg("操作成功",{icon:1});
	      location.href=FI.U('Admin/payments/index');
	  }else{
	        FI.msg(json.msg,{icon:2});
	  }
	});
}

$(function(){
	$('#payForm').validator({
      fields: {
      		/*默认验证*/
            pay_name: {rule:"required;",msg:{required:"请输入支付名称"},tip:"请输入支付名称",ok:"",},
            pay_desc: {rule:"required;",msg:{required:"请输入支付描述"},tip:"请输入支付描述",ok:"",},
            pay_order: {rule:"required;",msg:{required:"请输入排序号"},tip:"请输入排序号",ok:"",},
            /*微信验证*/
            appId: {rule:"required;",msg:{required:"请输入APPID"},tip:"请输入APPID",ok:"",},
            mchId: {rule:"required;",msg:{required:"请输入微信支付商户号(mch_id)"},tip:"请输入微信支付商户号(mch_id)",ok:"",},
            apiKey: {rule:"required;",msg:{required:"请输入API密钥(key)"},tip:"请输入API密钥(key)",ok:"",},
            appsecret: {rule:"required;",msg:{required:"请输入Appsecret"},tip:"请输入Appsecret",ok:"",},
            /*支付宝验证*/
            payAccount: {rule:"required;",msg:{required:"请输入支付宝账户"},tip:"请输入支付宝账户",ok:"",},
            parterID: {rule:"required;",msg:{required:"请输入合作者身份(parterID)"},tip:"请输入合作者身份(parterID)",ok:"",},
            parterKey: {rule:"required;",msg:{required:"请输入交易安全校验码(key"},tip:"请输入交易安全校验码(key",ok:"",},
        },
        valid:function(form){
          edit($('#id').val())
        },
  });

});