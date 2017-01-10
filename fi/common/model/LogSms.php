<?php
namespace fi\common\model;
/**
 * 短信日志类
 */
class LogSms extends Base{

	/**
	 * 写入并发送短讯记录
	 */
	public function sendSMS($sms_src,$phoneNumber,$content,$sms_func,$verfyCode){
		$USER = session('FI_USER');
		$user_id = empty($USER)?0:$USER['user_id'];
		$ip = request()->ip();
		
		//检测短信验证码验证是否正确
		if(FIConf("CONF.smsVerfy")==1){
			$smsverfy = input("post.smsVerfy");
			$rs = FIVerifyCheck($smsverfy);
			if(!$rs){
				return FIReturn("验证码不正确!");
			}
		}
		//检测是否超过每日短信发送数
		$date = date('Y-m-d');
		$smsRs = $this->field("count(sms_id) counts,max(create_time) create_time")
			 		  ->where(["sms_phone_number"=>$phoneNumber])
		 	          ->whereTime('create_time', 'between', [$date.' 00:00:00', $date.' 23:59:59'])->find();
		if($smsRs['counts']>(int)FIConf("CONF.smsLimit")){
			return FIReturn("请勿频繁发送短信验证!");
		}
		if($smsRs['create_time'] !='' && ((time()-strtotime($smsRs['create_time']))<120)){
			return FIReturn("请勿频繁发送短信验证!");
		}
		//检测IP是否超过发短信次数
		$ipRs = $this->field("count(sms_id) counts,max(create_time) create_time")
					 ->where(["sms_ip"=>$ip])
					 ->whereTime('create_time', 'between', [$date.' 00:00:00', $date.' 23:59:59'])->find();
		if($ipRs['counts']>(int)FIConf("CONF.smsLimit")){
			return FIReturn("请勿频繁发送短信验证!");
		}
		if($ipRs['create_time']!='' && ((time()-strtotime($ipRs['create_time']))<120)){
			return FIReturn("请勿频繁发送短信验证!");
		}
		$code = FISendSMS($phoneNumber,$content);
		$data = array();
		$data['sms_src'] = $sms_src;
		$data['sms_user_id'] = $user_id;
		$data['sms_phone_number'] = $phoneNumber;
		$data['sms_content'] = $content;
		$data['sms_return_code'] = $code;
		$data['sms_code'] = $verfyCode;
		$data['sms_ip'] = $ip;
		$data['sms_func'] = $sms_func;
		$data['create_time'] = date('Y-m-d H:i:s');
		$this->data($data)->save();
		if(intval($code)>0){
			return FIReturn("短信发送成功!",1);
		}else{
			return FIReturn("短信发送失败!");
		}
	}
}
