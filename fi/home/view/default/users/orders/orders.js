function waitPayByPage(p){
	$('#loading').show();
	var params = {};
	params = FI.getParams('.u-query');
	params.key = $.trim($('#key').val());
	params.p = p;
	$.post(FI.U('home/orders/waitPayByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = FI.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1 && json.data.Rows){
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
		        			 waitPayByPage(e.curr);
		        		 }
		        	 } 
		        });
	       	}else{
	       		$('#pager').empty();
	       	}
       	} 
	});
}
function waitReceiveByPage(p){
	$('#loading').show();
	var params = {};
	params = FI.getParams('.u-query');
	params.key = $.trim($('#key').val());
	params.p = p;
	$.post(FI.U('home/orders/waitReceiveByPage'),params,function(data,textStatus){
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
		        			 waitPayByPage(e.curr);
		        		 }
		        	 } 
		        });
	       	}else{
	       		$('#pager').empty();
	       	}
       	}    
	});
}
function toReceive(id){
	FI.confirm({content:'您确定已收货吗？',yes:function(){
		var ll = FI.load({msg:'正在提交信息，请稍候...'});
		$.post(FI.U('home/orders/receive'),{id:id},function(data){
			var json = FI.toJson(data);
			if(json.status>0){
				FI.msg(json.msg,{icon:1});
				waitReceiveByPage(0);
			    layer.close(ll);
			}else{
				FI.msg(json.msg,{icon:2});
			}
		});
	}})
}
function waitAppraiseByPage(p){
	$('#loading').show();
	var params = {};
	params = FI.getParams('.s-query');
	params.key = $.trim($('#key').val());
	params.p = p;
	$.post(FI.U('home/orders/waitAppraiseByPage'),params,function(data,textStatus){
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
		        			 waitPayByPage(e.curr);
		        		 }
		        	 } 
		        });
	       	}else{
	       		$('#pager').empty();
	       	}
       	}  
	});
}
function finishByPage(p){
	$('#loading').show();
	var params = {};
	params = FI.getParams('.s-query');
	params.key = $.trim($('#key').val());
	params.p = p;
	$.post(FI.U('home/orders/finishByPage'),params,function(data,textStatus){
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
		        			 waitPayByPage(e.curr);
		        		 }
		        	 } 
		        });
	       	}else{
	       		$('#pager').empty();
	       	}
       	}  
	});
}
function cancel(id,type){
	var ll = FI.load({msg:'正在加载信息，请稍候...'});
	$.post(FI.U('home/orders/toCancel'),{id:id},function(data){
		layer.close(ll);
		var w = FI.open({
			    type: 1,
			    title:"取消订单",
			    shade: [0.6, '#000'],
			    border: [0],
			    content: data,
			    area: ['500px', '260px'],
			    btn: ['提交', '关闭窗口'],
		        yes: function(index, layero){
		        	var reason = $.trim($('#reason').val());
		        	ll = FI.load({msg:'数据处理中，请稍候...'});
				    $.post(FI.U('home/orders/cancellation'),{id:id,reason:reason},function(data){
				    	layer.close(w);
				    	layer.close(ll);
				    	var json = FI.toJson(data);
						if(json.status==1){
							FI.msg(json.msg, {icon: 1});
							if(type==0){
								waitPayByPage(0);
							}else{
								waitReceiveByPage(0);
							}
						}else{
							FI.msg(json.msg, {icon: 2});
						}
				   });
		        }
			});
	});
}
function toReject(id){
	var ll = FI.load({msg:'正在加载信息，请稍候...'});
	$.post(FI.U('home/orders/toReject'),{id:id},function(data){
		layer.close(ll);
		var w = FI.open({
			    type: 1,
			    title:"拒收订单",
			    shade: [0.6, '#000'],
			    border: [0],
			    content: data,
			    area: ['500px', '300px'],
			    btn: ['提交', '关闭窗口'],
		        yes: function(index, layero){
		        	var params = {};
		        	params.reason = $.trim($('#reason').val());
		        	params.content = $.trim($('#content').val());
		        	params.id = id;
		        	if(params.id==10000 && params.conten==''){
		        		FI.msg('请输入拒收原因',{icon:2});
		        		return;
		        	}
		        	ll = FI.load({msg:'数据处理中，请稍候...'});
				    $.post(FI.U('home/orders/reject'),params,function(data){
				    	layer.close(w);
				    	layer.close(ll);
				    	var json = FI.toJson(data);
						if(json.status==1){
							FI.msg(json.msg, {icon: 1});
							waitReceiveByPage(0);
						}else{
							FI.msg(json.msg, {icon: 2});
						}
				   });
		        }
			});
	});
}
function changeRejectType(v){
	if(v==10000){
		$('#rejectTr').show();
	}else{
		$('#rejectTr').hide();
	}
}
function cancelByPage(p){
	$('#loading').show();
	var params = {};
	params = FI.getParams('.s-query');
	params.key = $.trim($('#key').val());
	params.p = p;
	$.post(FI.U('home/orders/cancelByPage'),params,function(data,textStatus){
		$('#loading').hide();
	    var json = FI.toJson(data);
	    $('.j-order-row').remove();
	    if(json.status==1 && json.data.Rows){
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
		        			 waitPayByPage(e.curr);
		        		 }
		        	 } 
		        });
	       	}else{
	       		$('#pager').empty();
	       	}
       	} 
	});
}
function abnormalByPage(p){
	$('#loading').show();
	var params = {};
	params = FI.getParams('.s-query');
	params.key = $.trim($('#key').val());
	params.p = p;
	$.post(FI.U('home/orders/abnormalByPage'),params,function(data,textStatus){
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
		        			 waitPayByPage(e.curr);
		        		 }
		        	 } 
		        });
	       	}else{
	       		$('#pager').empty();
	       	}
       	}    
	});
}
function view(id){
	location.href=FI.U('home/orders/detail','id='+id);
}
function complain(id){
	location.href=FI.U('home/ordercomplains/complain','order_id='+id);
}


/******************** 评价页面 ***********************/
function appraisesShowImg(id){
  layer.photos({
      photos: '#'+id
    });
}
function toAppraise(id){
  location.href=FI.U("home/orders/orderAppraise",{'oId':id});
}
//文件上传
function upload(n){
    var uploader =FI.upload({
        pick:'#filePicker'+n,
        formData: {dir:'appraises',isThumb:1},
        fileNumLimit:5,
        accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
        callback:function(f,file){
          var json = FI.toJson(f);
          if(json.status==1){
          var tdiv = $("<div style='width:75px;float:left;margin-right:5px;'>"+
                       "<img class='appraise_pic"+n+"' width='75' height='75' src='"+FI.conf.ROOT+"/"+json.savePath+json.thumb+"' v='"+json.savePath+json.name+"'></div>");
          var btn = $('<div style="position:relative;top:-80px;left:60px;cursor:pointer;" ><img src="'+FI.conf.ROOT+'/fi/home/View/default/img/seller_icon_error.png"></div>');
          tdiv.append(btn);
          $('#picBox'+n).append(tdiv);
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

function validator(n){
  $('#appraise-form'+n).validator({
         fields: {
                  score:  {
                    rule:"required",
                    msg:{required:"评分必须都大于0"},
                    ok:"",
                    target:"#score_error"+n,
                  },
                  
              },

            valid: function(form){
              var params = {};
              //获取该评价的内容
              params.content = $('#content'+n).val();
              // 获取该评价附件
              var photo=[];
              var images = [];
              $('.appraise_pic'+n).each(function(k,v){
                  var img = $(this).attr('v');
                      // 用于评价成功后的显示
                      photo.push(FI.conf.ROOT+'/'+img);
                  images.push(img);
              });
              params.images = images.join(',');
              //获取评分
              params.goods_score = $('.goods_score'+n).find('[name=score]').val();
              params.time_score = $('.time_score'+n).find('[name=score]').val();
              params.service_score = $('.service_score'+n).find('[name=score]').val();
              params.goods_id = $('#gid'+n).val();
              params.order_id = $('#oid'+n).val();
              params.goods_spec_id = $('#gsid'+n).val();

              $.post(FI.U('home/goodsAppraises/add'),params,function(data,dataStatus){
                var json = FI.toJson(data);
                if(json.status==1){
                   var thisbox = $('#app-box'+n);
                   var html = '<div class="appraise-area"><div class="appraise-item"><div class="appraise-title">商品评分：</div>';
                       html += '<div class="appraise-content">';
                       // 商品评分
                       for(var i=1;i<=params.goods_score;i++){
                          html +='<img src="'+FI.conf.STATIC+'/plugins/raty/img/star-on-big.png">';
                       }
                       html +='</div></div><div class="fi-clear"></div><div class="appraise-item"><div class="appraise-title"> 时效评分：</div>'
                       html +='<div class="appraise-content">'
                       // 时效评分
                       for(var i=1;i<=params.time_score;i++){
                          html +='<img src="'+FI.conf.STATIC+'/plugins/raty/img/star-on-big.png">';
                       }
                       html +='</div></div><div class="fi-clear"></div><div class="appraise-item"><div class="appraise-title">服务评分：</div>';
                       html +='<div class="appraise-content">';
                       // 服务评分
                       for(var i=1;i<=params.service_score;i++){
                          html +='<img src="'+FI.conf.STATIC+'/plugins/raty/img/star-on-big.png">';
                       }
                       html +='</div></div><div class="fi-clear"></div><div class="appraise-item"><div class="appraise-title">点评内容：</div>';
                       // 评价内容
                       html +='<div class="appraise-content">';
                        // 获取当前年月日
                       var  oDate = new Date();
                       var year = oDate.getFullYear()+'-';    //获取系统的年；
                       var month = oDate.getMonth()+1+'-';     //获取系统月份，由于月份是从0开始计算，所以要加1
                       var day = oDate.getDate();        // 获取系统日，
                       html +='<p>'+params.content+'['+year+month+day+']</p>';
                       html +='</div></div><div class="fi-clear"></div><div class="appraise-item"><div class="appraise-title"> </div>';
                       // 评价附件
                       html +='<div class="appraise-content">';
                       // 当前生成的相册id
                       var imgBoxId = "appraise-img-"+n;
                       html +='<div id='+imgBoxId+'>'
                       var count = photo.length;
                       for(var m=0;m<count;m++){
                          html += '<img src="'+photo[m].replace('.','_thumb.')+'" layer-src="'+photo[m]+'" width="75" height="75" style="margin-right:5px;">';
                       }
                       html +='</div></div></div></div>';
                       thisbox.html(html);
                       // 调用相册层
                       appraisesShowImg(imgBoxId);
                      
                }else{
                  FI.msg(json.msg,{icon:2});
                }
              });

        }
  });
}

/* 用户评价管理 */
function showImg(id){
  layer.photos({
      photos: '#img-file-'+id
    });
}
function userAppraise(p){
  $('#list').html('<img src="'+FI.conf.ROOT+'/fi/home/view/default/img/loading.gif">正在加载数据...');
  var params = {};
  params = FI.getParams('.s-query');
  params.key = $.trim($('#key').val());
  params.p = p;
  $.post(FI.U('home/goodsappraises/userAppraise'),params,function(data,textStatus){
      var json = FI.toJson(data);
      if(!json.data){
      	$('#list').html('');
      }
      if(json.status==1 && json.data){
          var gettpl = document.getElementById('tblist').innerHTML;
          laytpl(gettpl).render(json.data, function(html){
            $('#list').html(html);
            for(var g=0;g<=json.data.length;g++){
              showImg(g);
            }
           $('.j-lazyImg').lazyload({ effect: "fadeIn",failurelimit : 10,threshold: 200,placeholder:window.conf.ROOT+'/'+window.conf.GOODS_LOGO});
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
/**************** 用户投诉页面 *****************/
function userComplainInit(){
	 var uploader =FI.upload({
        pick:'#filePicker',
        formData: {dir:'complains',isThumb:1},
        fileNumLimit:5,
        accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
        callback:function(f,file){
          var json = FI.toJson(f);
          if(json.status==1){
          var tdiv = $("<div style='width:75px;float:left;margin-right:5px;'>"+
                       "<img class='complain_pic"+"' width='75' height='75' src='"+FI.conf.ROOT+"/"+json.savePath+json.thumb+"' v='"+json.savePath+json.name+"'></div>");
          var btn = $('<div style="position:relative;top:-80px;left:60px;cursor:pointer;" ><img src="'+FI.conf.ROOT+'/fi/home/View/default/img/seller_icon_error.png"></div>');
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
function saveComplain(historyURL){
   /* 表单验证 */
  $('#complainForm').validator({
              fields: {
                  complain_content: {
                    rule:"required",
                    msg:{required:"清输入投诉内容"},
                    tip:"清输入投诉内容",
                  },
                  complain_type: {
                    rule:"checked;",
                    msg:{checked:"投诉类型不能为空"},
                    tip:"请选择投诉类型",
                  }
                  
              },
            valid: function(form){
              var params = FI.getParams('.ipt');
              var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
              var img = [];
                  $('.complain_pic').each(function(){
                    img.push($(this).attr('v'));
                  });
                  params.complain_annex = img.join(',');

                  $.post(FI.U('home/orderComplains/saveComplain'),params,function(data,textStatus){
                    layer.close(loading);
                    var json = FI.toJson(data);
                    if(json.status=='1'){
                        FI.msg('您的投诉已提交，请留意信息回复', {icon: 6},function(){
                         //location.href = historyURL;
                         location.href = FI.U('home/ordercomplains/index');
                       });
                    }else{
                          FI.msg(json.msg,{icon:2});
                    }
                  });
        }
  });
}

/*********************** 用户投诉列表页面 ***************************/
function toView(id){
  location.href=FI.U('home/ordercomplains/getUserComplainDetail',{'id':id});
}
function complainByPage(p){
  $('#list').html('<img src="'+FI.conf.ROOT+'/fi/home/view/default/img/loading.gif">正在加载数据...');
  var params = {};
  params = FI.getParams('.s-query');
  params.key = $.trim($('#key').val());
  params.p = p;
  $.post(FI.U('home/ordercomplains/queryUserComplainByPage'),params,function(data,textStatus){
      var json = FI.toJson(data);
      if(json.status==1 && json.data){
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