var time = 0;
var isSend = false;
var myorm;
var emailForm;
var phoneForm;
var getemailForm;
var getphoneForm;
$(function(){
    $('#phoneVerify').validator({
      valid: function(form){
    	  var n=$('#VerifyId').val();
    	  getPhoneVerifys(n);
      }
    });
})
function veMyorm(){
	//修改密码
	myorm = $('#myorm').validator({
            fields: {
                newPass: {
                  rule:"required;length[6~20]",
                  msg:{required:"请输入新密码"},
                  tip:"请输入新密码"
                },
                reNewPass: {
                  rule:"required;length[6~20];match[newPass]",
                  msg:{required:"请再次输入新密码",match:"两次输入密码不匹配"},
                  tip:"请再次输入新密码"
                },
            },
          valid: function(form){
            var params = FI.getParams('.ipt');
            var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
            $.post(FI.U('home/users/passEdit'),params,function(data,textStatus){
              layer.close(loading);
              var json = FI.toJson(data);
              if(json.status=='1'){
                  FI.msg("操作成功",{icon:1,time:2000},function(){
                	  location.href=FI.U('home/users/security');
                  });
              }else{
                    FI.msg(json.msg,{icon:2});
              }
            });
      }
    })
}
function veemailForm(){
    //绑定邮箱
	emailForm = $('#emailForm').validator({
    	rules: {
            remote: function(element){
            	return $.post(FI.U('home/users/checkEmail'),{"login_name":element.value},function(data,textStatus){
            	});
            }	
    	},
        fields: {
        	user_email: {
		        rule:"required;email;remote;",
		        msg:{required:"请输入邮箱",email:"请输入有效的邮箱"},
		        tip:"请输入邮箱",
            },
            verifyCode: {
	            rule:"required",
	            msg:{required:"请输入验证码"},
	            tip:"请输入验证码",
	            target:"#verify"
            }
        },
        
      valid: function(form){
        var params = FI.getParams('.ipt');
        var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
        $.post(FI.U('home/users/getEmailVerify'),params,function(data,textStatus){
          layer.close(loading);
          var json = FI.toJson(data);
          if(json.status=='1'){
  			FI.msg('邮箱已发送，请注册查收');
  	        setTimeout(function(){ 
  	          $('#emailForm').hide();
  	          $('#inemail').html($('#user_email').val());
  	          $('#prompt').show();
  	        },1000);
          }else{
                FI.msg(json.msg,{icon:2});
                FI.getVerify('#verifyImg');
          }
        });
      }
    });
}
function vephoneForm(){
    //绑定手机号
	phoneForm = $('#phoneForm').validator({
      valid: function(form){
        var me = this;
        // ajax提交表单之前，先禁用submit
        me.holdSubmit();
        var params = FI.getParams('.ipt');
        var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
        $.post(FI.U('home/users/phoneEdito'),params,function(data,textStatus){
          layer.close(loading);
          var json = FI.toJson(data);
          if(json.status=='1'){
  	           location.href=FI.U('home/users/editPhoneSu','pr='+json.process);
          }else{
                FI.msg(json.msg,{icon:2});
                FI.getVerify('#verifyImg'); 
          }
        });
      }
    });
}
function vegetemailForm(){
	if(isSend )return;
	isSend = true;
    //修改邮箱
	getemailForm = $('#getemailForm').validator({
      valid: function(form){
        var params = FI.getParams('.ipt');
        var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
        $.post(FI.U('home/users/getEmailVerifyt'),params,function(data,textStatus){
          layer.close(loading);
          var json = FI.toJson(data);
          if(json.status=='1'){
  			FI.msg('邮箱已发送，请注册查收');
			time = 120;
			$('#timeSend1').attr('disabled', 'disabled').css('background','#e8e6e6');
			$('#timeSend1').html('发送验证邮件(120)');
			var task = setInterval(function(){
				time--;
				$('#timeSend1').html('发送验证邮件('+time+")");
				if(time==0){
					isSend = false;						
					clearInterval(task);
					$('#timeSend1').html("重新发送验证邮件");
					$('#timeSend1').removeAttr('disabled').css('background','#e23e3d');
				}
			},1000);
          }else{
                FI.msg(json.msg,{icon:2});
                FI.getVerify('#verifyImg');
          }
        });
      }
    });
}
function vegetphoneForm(){
    //修改手机号
	getphoneForm = $('#getphoneForm').validator({
      valid: function(form){
        var params = FI.getParams('.ipt');
        var loading = FI.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
        $.post(FI.U('home/users/phoneEditt'),params,function(data,textStatus){
          layer.close(loading);
          var json = FI.toJson(data);
          if(json.status=='1'){
  	        	location.href=FI.U('home/users/editPhoneSut');
          }else{
                FI.msg(json.msg,{icon:2});
                FI.getVerify('#verifyImg');
          }
        });
      }
    });
}
//发送手机验证码
function getPhoneVerify(n){
	if(!$('#user_phone').isValid())return;
	$('#VerifyId').val(n);
	if(window.conf.SMS_VERFY==1){
		FI.open({type: 1,title:"请输入验证码",shade: [0.6, '#000'],border: [0],content: $('#phoneVerify'),area: ['600px', '180px']});
	}else{
		getPhoneVerifys(n);
	}
}
function getPhoneVerifys(n){
	FI.msg('正在发送短信，请稍后...',{time:600000});
	var time = 0;
	var isSend = false;
	var params = FI.getParams('.ipt');
	$.post(FI.U('home/users/getPhoneVerify'+n),params,function(data,textStatus){
		var json = FI.toJson(data);
		if(isSend )return;
		isSend = true;
		if(json.status!=1){
			FI.msg(json.msg, {icon: 5});
			FI.getVerify('#verifyImg');
			time = 0;
			isSend = false;
		}if(json.status==1){
			FI.msg('短信已发送，请注册查收');
			layer.closeAll('page'); 
			time = 120;
			$('#timeObtain').attr('disabled', 'disabled').css('background','#e8e6e6');
			$('#timeObtain').html('获取手机验证码(120)').css('width','130px');
			var task = setInterval(function(){
				time--;
				$('#timeObtain').html('获取手机验证码('+time+")");
				if(time==0){
					isSend = false;						
					clearInterval(task);
					$('#timeObtain').html("重新获取验证码").css('width','100px');
					$('#timeObtain').removeAttr('disabled').css('background','#e23e3d');
				}
			},1000);
		}
	});
}
