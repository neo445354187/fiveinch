var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/userranks/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '会员等级图标', name: 'userrank_img', isSort: false,render:function(rowdata, rowindex, value){
	        	return '<img src="'+FI.conf.ROOT+'/'+rowdata['userrank_img']+'" height="28px" />';
	        }},
	        { display: '会员等级名称', name: 'rank_name', isSort: false},
	        { display: '积分上限', name: 'start_score', isSort: false},
	        { display: '积分下限', name: 'end_score', isSort: false},
	        { display: '折扣率(%)', name: 'rebate', isSort: false},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(FI.GRANT.HYDJ_02)h += "<a href='"+FI.U('admin/userranks/toEdit','id='+rowdata['rank_id'])+"'>修改</a> ";
	            if(FI.GRANT.HYDJ_03)h += "<a href='javascript:toDel(" + rowdata['rank_id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
}
function toDel(id){
	var box = FI.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(FI.U('admin/userranks/del'),{id:id},function(data,textStatus){
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

function editInit(){
 /* 表单验证 */
    $('#userRankForm').validator({
            fields: {
                rank_name: {
                  rule:"required",
                  msg:{required:"请输入会员等级名称"},
                  tip:"请输入会员等级名称",
                  ok:"",
                },
                userrank_img: {
                  rule:"required",
                  msg:{required:"请输上传会员图标"},
                  tip:"请输上传会员图标",
                  ok:"",
                }
                
            },

          valid: function(form){
            var params = FI.getParams('.ipt');
            var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(FI.U('admin/userranks/'+((params.rank_id==0)?"add":"edit")),params,function(data,textStatus){
              layer.close(loading);
              var json = FI.toAdminJson(data);
              if(json.status=='1'){
                  FI.msg("操作成功",{icon:1});
                  location.href=FI.U('Admin/userranks/index');
              }else{
                    FI.msg(json.msg,{icon:2});
              }
            });

      }

    });

//文件上传
FI.upload({
    pick:'#userranksPicker',
    formData: {dir:'userranks'},
    accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
    callback:function(f){
      var json = FI.toAdminJson(f);
      if(json.status==1){
      $('#uploadMsg').empty().hide();
      //保存上传的图片路径
      $('#userrank_img').val(json.savePath+json.thumb);
      $('#preview').html('<img src="'+FI.conf.ROOT+'/'+json.savePath+json.thumb+'" height="25" />');
      }else{
        FI.msg(json.msg,{icon:2});
      }
  },
  progress:function(rate){
      $('#uploadMsg').show().html('已上传'+rate+"%");
  }
});


};
  




		