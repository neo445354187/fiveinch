function waituserPayByPage(p){
	$('#loading').show();
	var params = {};
	params = FI.getParams('.s-ipt');
	params.key = $.trim($('#key').val());
	params.p = p;
	$.post(FI.U('home/orders/waituserPayByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = FI.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1 && json.data.Rows.length){
	    	json = json.data;
	       	var gettpl = document.getElementById('tblist').innerHTML;
	       	laytpl(gettpl).render(json.Rows, function(html){
	       		$(html).insertAfter('#loadingBdy');
	       		$('.gImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+FI.conf.GOODS_LOGO});
	       	});
	       	if(json.totalPage>1){
	       		laypage({
		        	 cont: 'pager', 
		        	 pages:json.Total, 
		        	 curr: json.CurrentPage,
		        	 skin: '#e23e3d',
		        	 groups: 3,
		        	 jump: function(e, first){
		        		 if(!first){
		        			 waitDivleryByPage(e.curr);
		        		 }
		        	 } 
		        });
	       	}else{
	       		$('#pager').empty();
	       	}
       	} 
	});
}
function waitDivleryByPage(p){
	$('#loading').show();
	var params = {};
	params = FI.getParams('.s-ipt');
	params.key = $.trim($('#key').val());
	params.p = p;
	$.post(FI.U('home/orders/waitDeliveryByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = FI.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1 && json.data.Rows.length){
	    	json = json.data;
	       	var gettpl = document.getElementById('tblist').innerHTML;
	       	laytpl(gettpl).render(json.Rows, function(html){
	       		$(html).insertAfter('#loadingBdy');
	       		$('.gImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+FI.conf.GOODS_LOGO});
	       	});
	       	if(json.totalPage>1){
	       		laypage({
		        	 cont: 'pager', 
		        	 pages:json.Total, 
		        	 curr: json.CurrentPage,
		        	 skin: '#e23e3d',
		        	 groups: 3,
		        	 jump: function(e, first){
		        		 if(!first){
		        			 waitDivleryByPage(e.curr);
		        		 }
		        	 } 
		        });
	       	}else{
	       		$('#pager').empty();
	       	}
       	} 
	});
}
function deliveredByPage(p){
  $('#loading').show();
  var params = {};
  params = FI.getParams('.s-ipt');
  params.key = $.trim($('#key').val());
  params.p = p;
  $.post(FI.U('home/orders/deliveredByPage'),params,function(data,textStatus){
    $('#loading').hide();
      var json = FI.toJson(data);
      $('.j-order-row').remove();
      if(json.status==1 && json.data.Rows.length){
        json = json.data;
          var gettpl = document.getElementById('tblist').innerHTML;
          laytpl(gettpl).render(json.Rows, function(html){
            $(html).insertAfter('#loadingBdy');
          $('.gImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+FI.conf.GOODS_LOGO});
          });
          if(json.totalPage>1){
            laypage({
               cont: 'pager', 
               pages:json.Total, 
               curr: json.CurrentPage,
               skin: '#e23e3d',
               groups: 3,
               jump: function(e, first){
                 if(!first){
                   deliveredByPage(e.curr);
                 }
               } 
            });
          }else{
            $('#pager').empty();
          }
        } 
  });
}
function editOrderMoney(id){
	var ll = FI.load({msg:'正在加载记录，请稍候...'});
	$.post(FI.U('home/orders/getMoneyByOrder'),{id:id},function(data){
    	layer.close(ll);
    	var json = FI.toJson(data);
		if(json.status>0 && json.data){
			var tmp = json.data;
			$('#m_orderNo').html(tmp.orderId);
			$('#m_goodsMoney').html(tmp.goodsMoney);
			$('#m_deliverMoney').html(tmp.deliverMoney);
			$('#m_totalMoney').html(tmp.totalMoney);
			$('#m_realTotalMoney').html(tmp.realTotalMoney);
			FI.open({type: 1,title:"修改订单价格",shade: [0.6, '#000'],border: [0],
				content: $('#editMoneyBox'),area: ['550px', '320px'],btn: ['确定','取消'],
				yes:function(index, layero){
					var newOrderMoney = $('#m_newOrderMoney').val();
					FI.confirm({content:'您确定修改后的订单价格为¥<span class="j-warn-order-money">'+newOrderMoney+'</span>吗？',yes:function(cf){
						var ll = FI.load({msg:'正在提交信息，请稍候...'});
						$.post(FI.U('home/orders/editOrderMoney'),{id:id,orderMoney:newOrderMoney},function(data){
							var json = FI.toJson(data);
							if(json.status>0){
								$('#newOrderMoney').val();
								FI.msg(json.msg,{icon:1});
								waituserPayByPage(0);
								layer.close(cf);
								layer.close(index);
						    	layer.close(ll);
							}else{
								FI.msg(json.msg,{icon:2});
							}
						});
					}});
				}
			});
		}
    });
}
function deliver(id){
	FI.open({type: 1,title:"请输入发货快递信息",shade: [0.6, '#000'], border: [0],
		content: $('#deliverBox'),area: ['350px', '180px'],btn: ['确定发货','取消'],
		yes:function(index, layero){
			var ll = FI.load({msg:'正在提交信息，请稍候...'});
			$.post(FI.U('home/orders/deliver'),{id:id},function(data){
				var json = FI.toJson(data);
				if(json.status>0){
					$('#deliverForm')[0].reset();
					FI.msg(json.msg,{icon:1});
					waitDivleryByPage(0);
					layer.close(index);
			    	layer.close(ll);
				}else{
					FI.msg(json.msg,{icon:2});
				}
			});
		}
    });
}
function finisedByPage(p){
	$('#loading').show();
	var params = {};
	params = FI.getParams('.s-ipt');
	params.key = $.trim($('#key').val());
	params.p = p;
	$.post(FI.U('home/orders/finishedByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = FI.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1 && json.data.Rows.length){
	    	json = json.data;
	       	var gettpl = document.getElementById('tblist').innerHTML;
	       	laytpl(gettpl).render(json.Rows, function(html){
	       		$(html).insertAfter('#loadingBdy');
         		$('.gImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+FI.conf.GOODS_LOGO});
	       	});
	       	if(json.totalPage>1){
	       		laypage({
		        	 cont: 'pager', 
		        	 pages:json.Total, 
		        	 curr: json.CurrentPage,
		        	 skin: '#e23e3d',
		        	 groups: 3,
		        	 jump: function(e, first){
		        		 if(!first){
		        			 waitDivleryByPage(e.curr);
		        		 }
		        	 } 
		        });
	       	}else{
	       		$('#pager').empty();
	       	}
       	}   
	});
}
function failureByPage(p){
	$('#loading').show();
	var params = {};
	params = FI.getParams('.s-ipt');
	params.key = $.trim($('#key').val());
	params.p = p;
	$.post(FI.U('home/orders/failureByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = FI.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1 && json.data.Rows.length){
	    	json = json.data;
	       	var gettpl = document.getElementById('tblist').innerHTML;
	       	laytpl(gettpl).render(json.Rows, function(html){
	       		$(html).insertAfter('#loadingBdy');
	       		$('.gImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+FI.conf.GOODS_LOGO});
	       	});
	       	if(json.totalPage>1){
	       		laypage({
		        	 cont: 'pager', 
		        	 pages:json.Total, 
		        	 curr: json.CurrentPage,
		        	 skin: '#e23e3d',
		        	 groups: 3,
		        	 jump: function(e, first){
		        		 if(!first){
		        			 failureByPage(e.curr);
		        		 }
		        	 } 
		        });
	       	}else{
	       		$('#pager').empty();
	       	}
       	}
	});
}
function agree(id){
	FI.confirm({content:'您同意用户拒收订单吗？',yes:function(index){
		var ll = FI.load({msg:'正在提交信息，请稍候...'});
		$.post(FI.U('home/orders/confer'),{id:id,status:1},function(data){
			var json = FI.toJson(data);
			if(json.status>0){
				FI.msg(json.msg,{icon:1});
				failureByPage(0);
				layer.close(index);
			    layer.close(ll);
			}else{
				FI.msg(json.msg,{icon:2});
			}
		});
	}})
}
function disagree(id){
	FI.open({type: 1,title:"请输入不同意原因",shade: [0.6, '#000'], border: [0],
		content: $('#failureBox'),area: ['450px', '220px'],btn: ['确定','取消'],
		yes:function(index, layero){
			var content = $.trim($('#content').val());
			var ll = FI.load({msg:'正在提交信息，请稍候...'});
			$.post(FI.U('home/orders/confer'),{id:id,status:0,content:content},function(data){
				var json = FI.toJson(data);
				if(json.status>0){
					$('#content').val();
					FI.msg(json.msg,{icon:1});
					failureByPage(0);
					layer.close(index);
			    	layer.close(ll);
				}else{
					FI.msg(json.msg,{icon:2});
				}
			});
		}
    });
}
function view(id){
	location.href=FI.U('home/orders/view','id='+id);
}


/********** 订单投诉列表 ***********/
function toView(id){
  location.href=FI.U('home/ordercomplains/getShopComplainDetail',{'id':id});
}
function toRespond(id){
  location.href=FI.U('home/ordercomplains/respond',{'id':id});
}

function complainByPage(p){
  $('#list').html('<img src="'+FI.conf.ROOT+'/fi/home/default/img/loading.gif">正在加载数据...');
  var params = {};
  params = FI.getParams('.s-query');
  params.key = $.trim($('#key').val());
  params.p = p;
  $.post(FI.U('home/ordercomplains/queryShopComplainByPage'),params,function(data,textStatus){
      var json = FI.toJson(data);
      if(json.status==1 && json.data.Rows){
          var gettpl = document.getElementById('tblist').innerHTML;
          laytpl(gettpl).render(json.data.Rows, function(html){
            $('#list').html(html);
          });
          if(json.totalPage>1){
            laypage({
               cont: 'pager', 
               pages:json.Total, 
               curr: json.CurrentPage,
               skin: '#e23e3d',
               groups: 3,
               jump: function(e, first){
                    if(!first){
                      userAppraise(e.curr);
                    }
                  } 
            });


          }else{
            $('#pager').empty();
          }
        }  
  });
}


/************  应诉页面  ************/
function respondInit(){
$('.gImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.ROOT+'/'+FI.conf.GOODS_LOGO});
  // 调用图像层
  layer.photos({
    photos: '#photos-complain'
  });

  var uploader =FI.upload({
        pick:'#filePicker',
        formData: {dir:'complains',isThumb:1},
        fileNumLimit:5,
        accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
        callback:function(f,file){
          var json = FI.toJson(f);
          if(json.status==1){
          var tdiv = $("<div style='width:75px;float:left;margin-right:5px;'>"+
                       "<img class='respond_pic"+"' width='75' height='75' src='"+FI.conf.ROOT+"/"+json.savePath+json.thumb+"' v='"+json.savePath+json.name+"'></div>");
          var btn = $('<div style="position:relative;top:-80px;left:60px;cursor:pointer;" ><img src="'+FI.conf.ROOT+'/fi/home/default/img/seller_icon_error.png"></div>');
          tdiv.append(btn);
          $('#picBox').append(tdiv);
          btn.on('click','img',function(){
            uploader.removeFile(file);
            $(this).parent().parent().remove();
            uploader.refresh();
          });
          }else{
            FI.msg(json.msg,{icon:2});
          }
      },
      progress:function(rate){
          $('#uploadMsg').show().html('已上传'+rate+"%");
      }
    });
}
function saveRespond(historyURL){
/* 表单验证 */
$('#respondForm').validator({
          fields: {
              respondContent: {
                rule:"required",
                msg:{required:"请输入应诉内容"},
                tip:"请输入应诉内容",
              },
              
              
          },
        valid: function(form){
          var params = FI.getParams('.ipt');
          var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
          var img = [];
              $('.respond_pic').each(function(){
                img.push($(this).attr('v'));
              });
              params.respondAnnex = img.join(',');
              $.post(FI.U('home/orderComplains/saveRespond'),params,function(data,textStatus){
                layer.close(loading);
                var json = FI.toJson(data);
                if(json.status=='1'){
                    FI.msg('您的应诉已提交，请留意信息回复', {icon: 6},function(){
                     //location.href = historyURL;
                     location.href = FI.U('home/ordercomplains/shopComplain');
                   });
                }else{
                      FI.msg(json.msg,{icon:2});
                }
              });
    }
});
}