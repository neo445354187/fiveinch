function login(){
	var loading = FI.msg('加载中', {icon: 16,time:60000});
	var params = FI.getParams('.ipt');
	$.post(FI.U('admin/index/checkLogin'),params,function(data,textStatus){
		layer.close(loading);
		var json = FI.toAdminJson(data);
		if(json.status=='1'){
			FI.msg("登录成功",{icon:1},function(){
				location.href=FI.U('admin/index/index');
			});
		}else{
			getVerify('#verifyImg');
			FI.msg(json.msg,{icon:2});			
		}
	});
}
getVerify = function(img){
	$(img).attr('src',FI.U('admin/index/getVerify','rnd='+Math.random()));
}