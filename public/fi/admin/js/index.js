$(window).resize(function(){
	var h = FI.pageHeight()-100;
    $('.l-tab-content').height(h);
    $('.l-tab-content-item').height(h);
    $('.fi-iframe').each(function(){
    	$(this).height(h-26);
    });
    $('.fi-accordion').each(function(){
    	liger.get($(this).attr('id')).setHeight(h-26);
    });
});
function changeTab(obj,n){
    var ltab = liger.get("fi-ltabs-"+n);
    ltab.setHeader("fi-ltab-"+n, $(obj).text());
    $('#fi-lframe-'+n).attr('src',$(obj).attr('url'));
}
function initTabMenus(menuId){
	$.post(FI.U('admin/index/getSubMenus'),{id:menuId},function(data,textStatus){
		 var json = FI.toAdminJson(data);
		 var html = [];
		 html.push('<div id="fi-layout-'+menuId+'" style="width:99.2%; margin:0 auto; margin-top:4px; ">'); 
		 html.push('<div position="left" id="fi-accordion-'+menuId+'" title="管理菜单" class="fi-accordion">');
		 if(json && json.length>0){
			 for(var i=0;i<json.length;i++){
       		 html.push('<div title="'+json[i]['menuName']+'">'); 
       		 html.push('     <div style=" height:7px;"></div>');
       		 if(json[i]['list']){
	        		 for(var j=0;j<json[i]['list'].length;j++){
		        		 html.push('<a class="fi-link" href="javascript:void(0)" url="'+FI.U(FI.blank(json[i]['list'][j]['privilegeUrl'],''))+'" onclick="javascript:changeTab(this,'+menuId+')">'+json[i]['list'][j]['menuName']+'</a>');  
	        		 }
       		 }
       		 html.push('     </div> ');
			 }
		 }
		 html.push('</div>');
		 html.push('<div id="fi-ltabs-'+menuId+'" position="center" class="fi-lnavtabs">'); 
		 html.push('  <div tabid="fi-ltab-'+menuId+'" title="我的主页" style="height:300px" >');
		 html.push('      <iframe frameborder="0" class="fi-iframe" id="fi-lframe-'+menuId+'" src="'+(initFrame?"":FI.U('admin/index/main'))+'"></iframe>');
		 html.push('  </div>');
		 html.push('</div>'); 
		 html.push('</div>');
		 initFrame = true;
		 $('#fi-tab-'+menuId).html(html.join(''));
		 $("#fi-layout-"+menuId).ligerLayout({
	         leftWidth: 190,
	         height: '100%',
	         space: 0
	     });
		 var height = $(".l-layout-center").height();
		 $("#fi-accordion-"+menuId).ligerAccordion({
		      height: height - 24, speed: null
		 });
		 $("#fi-ltabs-"+menuId).ligerTab({
		      height: height,
		      changeHeightOnResize:true,
		      showSwitchInTab : false,
		      showSwitch: false
	     });
		 if(initFrame)$('.l-tab-loading').remove();
	 });
}
var mMgrs = {},tab,initFrame = false;
$(function (){   
    tab = $("#fi-tabs").ligerTab({
         height: '100%',
         changeHeightOnResize:true,
         showSwitchInTab : false,
         showSwitch: false,
         onAfterSelectTabItem:function(n){
        	 var menuId = n.replace('fi-tab-','');
        	 if(!mMgrs['m'+menuId]){
	        	 var ltab = $("#fi-tab-"+menuId);
	        	 mMgrs['m'+menuId] = true;
	        	 if(menuId=='market'){
	        		 $('#fi-market').attr('src','http://market.***.com');
	        	 }else{
	        	     initTabMenus(menuId);
        	     }
        	 }
         }
    });
    var tabId = tab.getSelectedTabItemID();
    mMgrs['m'+tabId.replace('fi-tab-','')] = true;
    initTabMenus(tabId.replace('fi-tab-',''));
    $('.l-tab-content').height(FI.pageHeight()-70);
    $('.l-tab-content-item').height(FI.pageHeight()-70);
    $('.fi-iframe').each(function(){
    	$(this).height(h-10);
    });
    setTimeout(function(){
    	getLastVersion();
    },2000);
});
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
function logout(){
	FI.confirm({content:"您确定要退出该系统吗?",yes:function(){
		var loading = FI.msg('正在退出，请稍后...', {icon: 16,time:60000});
		$.post(FI.U('admin/index/logout'),FI.getParams('.ipt'),function(data,textStatus){
			layer.close(loading);
			var json = FI.toAdminJson(data);
			if(json.status=='1'){
				location.reload();
			}
		});
	}});
}
function clearCache(){
	var loading = FI.msg('正在清理缓存，请稍后...', {icon: 16,time:60000});
	$.post(FI.U('admin/index/clearcache'),{},function(data,textStatus){
		layer.close(loading);
		var json = FI.toAdminJson(data);
		if(json.status && json.status=='1'){
			FI.msg(json.msg,{icon:1});
		}else{
			FI.msg(json.msg,{icon:2});
		}
	});
}
function editPassBox(){
	var w = FI.open({type: 1,title:"修改密码",shade: [0.6, '#000'],border: [0],content:$('#editPassBox'),area: ['450px', '250px'],
	    btn: ['确定', '取消'],yes: function(index, layero){
	    	$('#editPassFrom').isValid(function(v){
	    		if(v){
		        	var params = FI.getParams('.ipt');
		        	var ll = FI.msg('数据处理中，请稍候...');
				    $.post(FI.U('admin/Staffs/editMyPass'),params,function(data){
				    	layer.close(ll);
				    	var json = FI.toAdminJson(data);
						if(json.status==1){
							FI.msg(json.msg, {icon: 1});
							layer.close(w);
						}else{
							FI.msg(json.msg, {icon: 2});
						}
				   });
	    		}})
        }
	});
}