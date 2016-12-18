//后门
function getLastVersion(){
	$.post(FI.U('admin/index/getVersion'),{},function(data,textStatus){
		var json = {};
		try{
	      if(typeof(data )=="object"){
			  json = data;
	      }else{
			  json = eval("("+data+")");
	      }
		}catch(e){}
	    if(json){
		   if(json.version && json.version!='same'){
			   $('#fi-version-tips').show();
			   $('#fi_version').html(json.version);
			   $('#fi_down').attr('href',json.downloadUrl);
		   }
		   if(json.accredit=='no'){
			   $('#fi-accredit-tips').show();
		   }
		   if(json.licenseStatus)$('#licenseStatus').html(json.licenseStatus);
	   }
	});
}
$(function(){
    getLastVersion();
})