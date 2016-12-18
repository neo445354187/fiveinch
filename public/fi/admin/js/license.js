//后门
function edit(){
	var params = {};
	params.license = $('#license').val();
	$('#licenseTr').hide();
	$('#editFrom').isValid(function(v){
	if(v){
		var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
		$.post(FI.U('admin/index/verifyLicense'),params,function(data,textStatus){
			layer.close(loading);
			var json = FI.toAdminJson(data);
			if(json.status=='1'){
				$('#licenseTr').show();
				$('#licenseStatus').html(json.license.licenseStatus);
			}else{
				FI.msg("操作成功",{icon:1});
			}
		});
	}});
}  