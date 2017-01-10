var grid;
function initGrid(){
	grid = $("#maingrid").ligerGrid({
		url:FI.U('admin/Users/pageQuery'),
		pageSize:FI.pageSize,
		pageSizeOptions:FI.pageSizeOptions,
		height:'99%',
        width:'100%',
        minColToggle:6,
        rownumbers:true,
        columns: [
	        { display: '账号', name: 'login_name', isSort: false},
	        { display: '用户名', name: 'user_name', isSort: false},
	        { display: '手机号码', name: 'user_phone', isSort: false},
	        { display: '电子邮箱', name: 'user_email', isSort: false},
	        { display: '积分', name: 'user_score', isSort: false},
	        { display: '等级', name: 'rebate', isSort: false},
	        { display: '注册时间', name: 'create_time', isSort: false},
	        { display: '状态', name: 'user_status', isSort: false, render:function(rowdata, rowindex, value){
	        	return (value==1)?'启用':'停用';
	        }},
	        { display: '操作', name: 'op',isSort: false,render: function (rowdata, rowindex, value){
	            var h = "";
	            if(FI.GRANT.HYGL_02)h += "<a href='"+FI.U('admin/Users/toEdit','id='+rowdata['user_id'])+"'>修改</a> ";
	            if(FI.GRANT.HYGL_03)h += "<a href='javascript:toDel(" + rowdata['user_id'] + ")'>删除</a> "; 
	            return h;
	        }}
        ]
    });
	
	
	
}
function toDel(id){
	var box = FI.confirm({content:"您确定要删除该记录吗?",yes:function(){
	           var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
	           	$.post(FI.U('admin/Users/del'),{id:id},function(data,textStatus){
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

function userQuery(){
				var query = FI.getParams('.query');
			    grid.set('url',FI.U('admin/Users/pageQuery',query));
			}



function editInit(){
	 /* 表单验证 */
    $('#userForm').validator({
            dataFilter: function(data) {
                if (data.ok === '该登录账号可用' ) return "";
                else return "已被注册";
            },
            rules: {
                login_name: function(element) {
                    return /\w{5,}/.test(element.value) || '账号应为5-16字母、数字或下划线';
                },
                myRemote: function(element){
                    return $.post(FI.U('admin/users/checkLoginKey'),{'login_name':element.value,'user_id':$('#user_id').val()},function(data,textStatus){});
                }
            },
            fields: {
                login_name: {
                  rule:"required;login_name;myRemote",
                  msg:{required:"请输入会员账号"},
                  tip:"请输入会员账号",
                  ok:"",
                },
                
                user_phone: {
                  rule:"required;mobile;myRemote",
                  msg:{required:"请输入手机号"},
                  tip:"请输入手机号",
                  ok:"",
                },
                user_email: {
                  rule:"required;email;myRemote",
                  msg:{required:"请输入邮箱"},
                  tip:"请输入邮箱",
                  ok:"",
                },
                user_score: {
                  rule:"integer[+0]",
                  msg:{integer:"当前积分只能是正整数"},
                  tip:"当前积分只能是正整数",
                  ok:"",
                },
                user_total_score: {
                  rule:"match[gt, user_score];integer[+0];",
                  msg:{integer:"当前积分只能是正整数",match:'会员历史积分必须大于会员积分'},
                  tip:"当前积分只能是正整数",
                  ok:"",
                },
                user_qq: {
                  rule:"integer[+]",
                  msg:{integer:"QQ只能是数字"},
                  tip:"QQ只能是数字",
                  ok:"",
                },
                
            },

          valid: function(form){
            var params = FI.getParams('.ipt');
            var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(FI.U('admin/Users/'+((params.user_id==0)?"add":"edit")),params,function(data,textStatus){
              layer.close(loading);
              var json = FI.toAdminJson(data);
              if(json.status=='1'){
                  FI.msg("操作成功",{icon:1});
                  location.href=FI.U('Admin/Users/index');
              }else{
                    FI.msg(json.msg,{icon:2});
              }
            });

      }

    });



//上传头像
  FI.upload({
      pick:'#ad_filePicker',
      formData: {dir:'users'},
      accept: {extensions: 'gif,jpg,jpeg,bmp,png',mimeTypes: 'image/*'},
      callback:function(f){
        var json = FI.toAdminJson(f);
        if(json.status==1){
        $('#uploadMsg').empty().hide();
        //将上传的图片路径赋给全局变量
        $('#user_photo').val(json.savePath+json.thumb);
        $('#preview').html('<img src="'+FI.conf.ROOT+'/'+json.savePath+json.thumb+'"  height="152" />');
        }else{
          FI.msg(json.msg,{icon:2});
        }
    },
    progress:function(rate){
        $('#uploadMsg').show().html('已上传'+rate+"%");
    }
    });
}