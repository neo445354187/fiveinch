function checkChks(obj,cobj){
	FI.checkChks(obj,cobj);
	$(cobj).each(function(){
		id = $(this).val();
		if(obj.checked){
			$(this).addClass('selected');
		}else{
			$(this).removeClass('selected');
		}
		var cid = $(this).find(".j-chk").val();
		if(cid!=''){
		    FI.changeCartGoods(cid,$('#buyNum_'+cid).val(),obj.checked?1:0);
		    statCartMoney();
	    }
	})
}
function statCartMoney(){
	var cartMoney = 0,goodsTotalPrice,id;
	$('.j-gchk').each(function(){
		id = $(this).val();
		goodsTotalPrice = parseFloat($(this).attr('mval'))*parseInt($('#buyNum_'+id).val());
		$('#tprice_'+id).html(goodsTotalPrice);
		if($(this).prop('checked')){	
			cartMoney = cartMoney + goodsTotalPrice;
		}
	});
	$('#total_money').html(cartMoney);
	checkGoodsBuyStatus();
}
function checkGoodsBuyStatus(){
	var cart_num = 0,stockNum = 0,cart_id = 0;
	$('.j-gchk').each(function(){
		cart_id = $(this).val();
		cart_num = parseInt($('#buyNum_'+cart_id).val(),10);
		stockNum = parseInt($(this).attr('sval'),10);;
		if(stockNum < 0 || stockNum < cart_num){
			if($(this).prop('checked')){
				$(this).parent().parent().css('border','2px solid red');
			}else{
				$(this).parent().parent().css('border','0px solid #eeeeee');
				$(this).parent().parent().css('border-bottom','1px solid #eeeeee');
			}
			if(stockNum < 0){
				$('#gchk_'+cart_id).attr('allowbuy',0);
				$('#err_'+cart_id).css('color','red').html('库存不足');
			}else{
				$('#gchk_'+cart_id).attr('allowbuy',1);
				$('#err_'+cart_id).css('color','red').html('购买量超过库存');
			}
		}else{
			$('#gchk_'+cart_id).attr('allowbuy',10);
			$(this).parent().parent().css('border','0px solid #eeeeee');
			$(this).parent().parent().css('border-bottom','1px solid #eeeeee');
			$('#err_'+cart_id).html('');
		}
	});
}
function toSettlement(){
	var isChk = false;
	$('.j-gchk').each(function(){
		if($(this).prop('checked'))isChk = true;
	});
	if(!isChk){
		FI.msg('请选择要结算的商品!',{icon:1});
		return;
	}
	var msg = '';
	$('.j-gchk').each(function(){
		if($(this).prop('checked')){
			if($(this).attr('allowbuy')==0){
				msg = '所选商品库存不足';
				return;
			}else if($(this).attr('allowbuy')==1){
				msg = '所选商品购买量大于商品库存';
				return;
			}
		}
	})
	if(msg!=''){
		FI.msg(msg,{icon:2});
		return;
	}
	location.href=FI.U('home/carts/settlement');
}

function addrBoxOver(t){
	$(t).addClass('radio-box-hover');
	$(t).find('.operate-box').show();
}
function addrBoxOut(t){
	$(t).removeClass('radio-box-hover');
	$(t).find('.operate-box').hide();
}



function setDeaultAddr(id){
	$.post(FI.U('home/useraddress/setDefault'),{id:id},function(data){
		var json = FI.toJson(data);
		if(json.status==1){
			getAddressList();
			changeAddrId(id);
		}
	});
}


function changeAddrId(id){
	$.post(FI.U('home/useraddress/getById'),{id:id},function(data){
		var json = FI.toJson(data);
		if(json.status==1){
			inEffect($('#addr-'+id),1);
			$('#s_address_id').val(json.data.address_id);
			$('#s_area_id').val(json.data.area_id);
			FI.setValues(json.data);
		}
	})
}

function delAddr(id){
	FI.confirm({content:'您确定要删除该地址吗？',yes:function(index){
		$.post(FI.U('home/useraddress/del'),{id:id},function(data,textStatus){
		     var json = FI.toJson(data);
		     if(json.status==1){
		    	 FI.msg(json.msg,{icon:1});
		    	 getAddressList();
		     }else{
		    	 FI.msg(json.msg,{icon:2});
		     }
		});
	}});
}

function getAddressList(obj){
	var id = $('#s_address_id').val();
	var load = FI.load({msg:'正在加载记录，请稍后...'});
	$.post(FI.U('home/useraddress/listQuery'),{rnd:Math.random()},function(data,textStatus){
		 layer.close(load);
	     var json = FI.toJson(data);
	     if(json.status==1){
	    	 if(json.data && json.data && json.data.length){
	    		 var html = [],tmp;
	    		 for(var i=0;i<json.data.length;i++){
	    			 tmp = json.data[i];
	    			 var selected = (id==tmp.address_id)?'j-selected':'';
	    			 html.push(
	    					 '<div class="fi-frame1 '+selected+'" onclick="javascript:changeAddrId('+tmp.address_id+')" id="addr-'+tmp.address_id+'" >'+tmp.user_name+'<i></i></div>',
	    					 '<li class="radio-box" onmouseover="addrBoxOver(this)" onmouseout="addrBoxOut(this)">',
	    					 tmp.user_name,
	    					 '&nbsp;&nbsp;',
	    					 tmp.area_name+tmp.user_address,
	    					 '&nbsp;&nbsp;&nbsp;&nbsp;',
	    					 tmp.user_phone
	    					 )
	    			if(tmp.is_default==1){
	    				html.push('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="j-default">默认地址</span>')
	    			}		
	    			html.push('<div class="operate-box">');
	    			if(tmp.is_default!=1){
	    				html.push('<a href="javascript:;" onclick="setDeaultAddr('+tmp.address_id+')">设为默认地址</a>&nbsp;&nbsp;');
	    			}
	    			html.push('<a href="javascript:void(0)" onclick="javascript:toEditAddress('+tmp.address_id+',this,1,1)">编辑</a>&nbsp;&nbsp;');
	    			if(json.data.length>1){
	    				html.push('<a href="javascript:void(0)" onclick="javascript:delAddr('+tmp.address_id+',this)">删除</a></div>');
	    			}
	    			html.push('<div class="fi-clear"></div>','</li>');
	    		 }
	    		 html.push('<a style="color:#1c9eff" onclick="editAddress()" href="javascript:;">收起地址</a>'); 


	    		 $('#addressList').html(html.join(''));
	    	 }else{
	    		 $('#addressList').empty();
	    	 }
	     }else{
	    	 $('#addressList').empty();
	     }
	})
}












function inEffect(obj,n){
	$(obj).addClass('j-selected').siblings('.fi-frame'+n).removeClass('j-selected');
}
function editAddress(){
	var isNoSelected = false;
	$('.j-areas').each(function(){
		isSelected = true;
		if($(this).val()==''){
			isNoSelected = true;
			return;
		}
	})
	if(isNoSelected){
		FI.msg('请选择完整收货地址！',{icon:2});
		return;
	}
	layer.close(layerbox);
	var load = FI.load({msg:'正在提交数据，请稍后...'});
	var params = FI.getParams('.j-eipt');
	params.area_id = FI.ITGetAreaVal('j-areas');
	$.post(FI.U('home/useraddress/'+((params.address_id>0)?'toEdit':'add')),params,function(data,textStatus){
		layer.close(load);
		var json = FI.toJson(data);
	     if(json.status==1){
	    	 $('.j-edit-box').hide();
	    	 $('.j-list-box').hide();
	    	 $('.j-show-box').show();
	    	 if(params.address_id==0){
	    		 $('#s_address_id').val(json.data.address_id);
	    	 }else{
	    		 $('#s_address_id').val(params.address_id);
	    	 }
	    	 var area_ids = FI.ITGetAllAreaVals('area_0','j-areas');
	    	 getCartMoney(area_ids[1]);
	    	 var area_names = [];
	    	 $('.j-areas').each(function(){
	    		 area_names.push($('#'+$(this).attr('id')+' option:selected').text());
	    	 })
	    	 $('#s_user_name').html(params.user_name+'<i></i>');
	    	 $('#s_address').html(params.user_name+'&nbsp;&nbsp;&nbsp;'+area_names.join('')+'&nbsp;&nbsp;'+params.user_address+'&nbsp;&nbsp;'+params.user_phone);
	    	 if(params.is_default==1){
	    		 $('#isdefault').html('默认地址').addClass('j-default');
	    	 }else{
	    		 $('#isdefault').html('').removeClass('j-default');
	    	 }
	     }else{
	    	 FI.msg(json.msg,{icon:2});
	     }
	});
}
var layerbox;
function showEditAddressBox(){
	getAddressList();
	toEditAddress();
}
function emptyAddress(obj,n){
	inEffect(obj,n);
	$('#addressForm')[0].reset();
	$('#s_address_id').val(0);
	$('#address_id').val(0);
	$("select[id^='area_0_']").remove();

	layerbox =	layer.open({
					title:'用户地址',
					type: 1,
					area: ['800px', '300px'],
					content: $('.j-edit-box')
					});
}
function toEditAddress(id,obj,n,flag,type){
	inEffect(obj,n);
	id = (id>0)?id:$('#s_address_id').val();
	$.post(FI.U('home/useraddress/getById'),{id:id},function(data,textStatus){
	     var json = FI.toJson(data);
	     if(json.status==1){
	     	if(flag){
		     	layerbox =	layer.open({
					title:'用户地址',
					type: 1,
					area: ['800px', '300px'], //宽高
					content: $('.j-edit-box')
				});
	     	}
	     	if(type!=1){
				 $('.j-list-box').show();
		    	 $('.j-show-box').hide();
	     	}
	    	 FI.setValues(json.data);
	    	 $('input[name="addrUserPhone"]').val(json.data.user_phone)
	    	 $("select[id^='area_0_']").remove();
	    	 if(id>0){
		    	 var area_id_path = json.data.area_id_path.split("_");
		     	 $('#area_0').val(area_id_path[0]);
		     	 var aopts = {id:'area_0',val:area_id_path[0],childIds:area_id_path,className:'j-areas'}
		 		 FI.ITSetAreas(aopts);
	    	 }
	     }else{
	    	 FI.msg(json.msg,{icon:2});
	     }
	});
}
function getCartMoney(area_id2){
	var deliver_type = $('#deliver_type').val();
	if(deliver_type==1){
		$('#deliver_money').html(0);
		$('#total_money').html($('#total_money').attr('v'));
	}else{
		var load = FI.load({msg:'正在计算运费，请稍后...'});
		$.post(FI.U('home/carts/getCartMoney'),{area_id2:area_id2,rnd:Math.random()},function(data,textStatus){
			layer.close(load);  
			var json = FI.toJson(data);
		     if(json.status==1){
		    	 var shopFreight = 0;
		    	 for(var key in json.shops){
		    		 shopFreight = shopFreight + json.shops[key];
		    	 }
		    	 $('#deliver_money').html(shopFreight);
		 		 $('#total_money').html(json.total);
		     }
		});
	}
}
function changeDeliverType(n,index,obj){
	changeSelected(n,index,obj);
	var area_id2 = $('#area_id2').val();
	getCartMoney(area_id2);
}
function submitOrder(){
	var params = FI.getParams('.j-ipt');
	var load = FI.load({msg:'正在提交，请稍后...'});
	$.post(FI.U('home/orders/submit'),params,function(data,textStatus){
		layer.close(load);   
		var json = FI.toJson(data);
	    if(json.status==1){
	    	 FI.msg(json.msg,{icon:1},function(){
	    		 location.href=FI.U('home/orders/succeed','id='+json.data);
	    	 });
	    }else{
	    	FI.msg(json.msg,{icon:2});
	    }
	});
}



FI.showhide = function(t,str,obj){
	var s = str.split(',');
	if(t){
		for(var i=0;i<s.length;i++){
		   $(s[i]).show();
		}
	}else{
		for(var i=0;i<s.length;i++){
		   $(s[i]).hide();
		}
	}
	s = null;
	changeSelected(t,'is_invoice',obj);
}
function changeSelected(n,index,obj){
	$('#'+index).val(n);
	inEffect(obj,2);
}



function getPayUrl(){
	var params = {};
		params.id = $("#oId").val();
		params.isBatch = $("#isBatch").val();
		params.pay_code = $.trim($("#pay_code").val());
	if(params.pay_code==""){
		FI.msg('请先选择支付方式', {icon: 5});
		return;
	}
	jQuery.post(FI.U('home/'+params.pay_code+'/get'+params.pay_code+"URL"),params,function(data) {
		var json = FI.toJson(data);
		if(json.status==1){
			if(params.pay_code=="weixinpays"){
				location.href = json.url;
			}else{
				window.open(json.url);
			}
		}else{
			FI.msg('您的订单已支付!', {icon: 5});
			setTimeout(function(){				
				window.location = FI.U('home/orders/waitdelivery');
			},1500);
		}
	});
}