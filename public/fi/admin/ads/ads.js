var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/Ads/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '标题', name: 'ad_name', isSort: false},
	        { display: '广告位置', name: 'ad_position_id', isSort: false,render:function(rowdata, rowindex, value){
	        	return rowdata['position_name'];
	        }},
	        { display: '广告网址', name: 'ad_url', isSort: false},
	        { display: '广告开始日期', name: 'ad_start_date', isSort: false},
	        { display: '广告结束日期', name: 'ad_end_date', isSort: false},
	        { display: '图标', name: 'ad_file', height: '300', isSort: false,render:function(rowdata, rowindex, value){
            var ad_file = rowdata['ad_file'].split(',');
              return'<img src="'+FI.conf.ROOT+'/'+ad_file[0]+'" height="28px" />';
	        }},
          { display: '点击数', name: 'ad_click_num', isSort: false},
	        { display: '排序号', name: 'ad_sort', isSort: false,render:function(rowdata, rowindex, value){
              return '<span style="cursor:pointer;" ondblclick="changeSort(this,'+rowdata["ad_id"]+');">'+value+'</span>';
          }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	        	var h = "";
	            if(FI.GRANT.GGGL_02)h += "<a href='"+FI.U('admin/Ads/toEdit','id='+rowdata['ad_id'])+"'>修改</a> ";
	            if(FI.GRANT.GGGL_03)h += "<a href='javascript:toDel(" + rowdata['ad_id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}
function toDel(id){
	var box = FI.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(FI.U('admin/Ads/del'),{id:id},function(data,textStatus){
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

var oldSort;
function changeSort(t,id){
 $(t).attr('ondblclick'," ");
var html = "<input type='text' id='sort-"+id+"' style='width:30px;' onblur='doneChange(this,"+id+")' value='"+$(t).html()+"' />";
 $(t).html(html);
 $('#sort-'+id).focus();
 $('#sort-'+id).select();
 oldSort = $(t).html();
}
function doneChange(t,id){
  var sort = ($(t).val()=='')?0:$(t).val();
  if(sort==oldSort){
    $(t).parent().attr('ondblclick','changeSort(this,'+id+')');
    $(t).parent().html(parseInt(sort));
    return;
  }
  $.post(FI.U('admin/ads/changeSort'),{id:id,ad_sort:sort},function(data){
    var json = FI.toAdminJson(data);
    if(json.status==1){
        $(t).parent().attr('ondblclick','changeSort(this,'+id+')');
        $(t).parent().html(parseInt(sort));
    }
  });
}


		
//查询
function adsQuery(){
		var query = FI.getParams('.query');
	    grid.set('url',FI.U('admin/ads/pageQuery',query));
}

function editInit(){
  //文件上传
	FI.upload({
  	  pick:'#ad_filePicker',
  	  formData: {dir:'adspic'},
      compress:false,//默认不对图片进行压缩
  	  accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
  	  callback:function(f){
  		  var json = FI.toAdminJson(f);
  		  if(json.status==1){
  			$('#uploadMsg').empty().hide();
        var html = '<img src="'+FI.conf.ROOT+'/'+json.savePath+json.thumb+'" />';
        $('#preview').html(html);
        // 图片路径
        $('#ad_file').val(json.savePath+json.thumb);
  		  }
	  },
	  progress:function(rate){
	      $('#uploadMsg').show().html('已上传'+rate+"%");
	  }
    });
  

 /* 表单验证 */
    $('#adsForm').validator({
            fields: {
                ad_position_id: {
                  rule:"required",
                  msg:{required:"请选择广告位置"},
                  tip:"请选择广告位置",
                  ok:"验证通过",
                },
                ad_name: {
                  rule:"required;",
                  msg:{required:"广告标题不能为空"},
                  tip:"请输入广告标题",
                  ok:"验证通过",
                },
                ad_file: {
                  rule:"required;",
                  msg:{required:"请上传广告图片"},
                  tip:"请上传广告图片",
                  ok:"",
                },
                ad_start_date: {
                  rule:"required;match(lt, ad_end_date, date)",
                  msg:{required:"请选择广告开始时间",match:"必须小于广告结束时间"},
                  ok:"验证通过",
                },
                ad_end_date: {
                  rule:"required;match(gt, ad_start_date, date)",
                  msg:{required:"请选择广告结束时间",match:"必须大于广告开始时间"},
                  ok:"验证通过",
                }
            },
          valid: function(form){
            var params = FI.getParams('.ipt');
            var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(FI.U('admin/Ads/'+((params.ad_id==0)?"add":"edit")),params,function(data,textStatus){
              layer.close(loading);
              var json = FI.toAdminJson(data);
              if(json.status=='1'){
                  FI.msg("操作成功",{icon:1});
                  location.href=FI.U('Admin/Ads/index');
              }else{
                    FI.msg(json.msg,{icon:2});
              }
            });
      }
    });
}


var positionInfo;
/*获取地址*/
function addPosition(pType, val, getSize)
{
    $.post(FI.U('admin/Adpositions/getPositon'),{'position_type':pType},function(data,textStatus){
        positionInfo = data;
        var html='<option value="">请选择</option>';
        $(data).each(function(k,v){
			var selected;
            if(v.position_id==val){
              selected = 'selected="selected"';
              getPhotoSize(v.position_id);
            }
            html +='<option '+selected+' value="'+v.position_id+'">'+v.position_name+'</option>';
        });
        $('#ad_position_id').html(html);
    })
}
/*获取图片尺寸 以及设置图片显示方式*/
function getPhotoSize(pType)
{
  $(positionInfo).each(function(k,v){
      if(v.position_id==pType){
        $('#img_size').html(v.position_width+'x'+v.position_height);
        if(v.position_width>v.position_height){
             $('.ads-h-list').removeClass('ads-h-list').addClass('ads-w-list');
         }
      }
  });

}