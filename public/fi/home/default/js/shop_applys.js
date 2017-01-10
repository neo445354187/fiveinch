function getClose(){
	layer.closeAll();
}

function showProtocol(){
	window.open(FI.U('home/shopapplys/protocol'));
}

var time = 0;
var isSend = false;
var is_use = false;
var index2 = null;
function getShopCode(){
	var params = {};
	if(!$('#user_phone2').isValid())return;
	if(window.conf.SMS_VERFY==1){
	    index2 = FI.open({
	    	type: 1,
	    	title:"请输入验证码",
	    	offset: '250px',
	    	shade: [0.6, '#000'],
	    	border: [0],
	    	content: $('#shopVerifys'),
	    	area: ['500px', '180px']
	    });
	}else{
		shopVerifys();
	}
}

function shopVerifys(){
	if(isSend )return;
	isSend = true;
	var params = FI.getParams('.fi_ipt2');
	FI.msg('正在发送短信，请稍后...',{time:600000});
	$.post(FI.U('home/shopapplys/getPhoneVerifyCode'),params,function(data,textStatus){
		var json = FI.toJson(data);
		if(json.status!=1){
			FI.msg(json.msg, {icon: 5});
			time = 0;
			isSend = false;
			FI.getVerify('#verifyImg3');
		}if(json.status==1){
			layer.close(index2);
			FI.msg('短信已发送，请注册查收');
			time = 120;
			$('#timeTips').css('width','100px');
			$('#timeTips').html('获取验证码(120)');
			$('#mobileCode').val(json.phoneVerifyCode);
			var task = setInterval(function(){
				time--;
				$('#timeTips').html('获取验证码('+time+")");
				if(time==0){
					isSend = false;						
					clearInterval(task);
					$('#timeTips').html("重新获取验证码");
				}
			},1000);
		}
	});
}

$(function() {
	$('#apply_form').validator({
        fields: {
        	protocol: {
	    		rule: 'checked();'
            },
		    verifyCode: {
		        rule:"required",
		        msg:{required:"请输入短信验证码"},
		        tip:"请输入短信验证码",
		        target:"#mobileCodeTips"
		    }
        },
	    // 表单验证通过后，ajax提交
	    valid: function(form){
	        var me = this;
	        me.holdSubmit();
	        var params = FI.getParams('.fi_ipt2');
	        $("#reg_butt").css('color', '#999').text('正在提交..');
	        $.post(FI.U('home/shopapplys/apply'),params,function(data,textStatus){
	    		var json = FI.toJson(data);
	    		if(json.status>0){
	    			FI.msg('申请提交成功，我们将会尽快与您联系!', {icon: 6}, function(){
	    				$('#fi-shopapp').hide();
	    				layer.closeAll();
	       			});
	    		}else{
	    			me.holdSubmit(false);
	    			FI.msg(json.msg, {icon: 5});
	    		}
	    		FI.getVerify('#verifyImg3');
	    	});
	    }
	});
    $('#shopVerifys').validator({
        valid: function(form){
        	shopVerifys();
        }
      });
});

    