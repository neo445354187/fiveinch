<?php
namespace fi\common\model;
/**
 * 短信日志类
 */
class LogSms extends Base{

	/**
	 * 写入并发送短讯记录
	 */
	public function sendSMS($smsSrc,$phoneNumber,$content,$smsFunc,$verfyCode){
		$USER = session('FI_USER');
		$userId = empty($USER)?0:$USER['userId'];
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
		$smsRs = $this->field("count(smsId) counts,max(createTime) createTime")
			 		  ->where(["smsPhoneNumber"=>$phoneNumber])
		 	          ->whereTime('createTime', 'between', [$date.' 00:00:00', $date.' 23:59:59'])->find();
		if($smsRs['counts']>(int)FIConf("CONF.smsLimit")){
			return FIReturn("请勿频繁发送短信验证!");
		}
		if($smsRs['createTime'] !='' && ((time()-strtotime($smsRs['createTime']))<120)){
			return FIReturn("请勿频繁发送短信验证!");
		}
		//检测IP是否超过发短信次数
		$ipRs = $this->field("count(smsId) counts,max(createTime) createTime")
					 ->where(["smsIP"=>$ip])
					 ->whereTime('createTime', 'between', [$date.' 00:00:00', $date.' 23:59:59'])->find();
		if($ipRs['counts']>(int)FIConf("CONF.smsLimit")){
			return FIReturn("请勿频繁发送短信验证!");
		}
		if($ipRs['createTime']!='' && ((time()-strtotime($ipRs['createTime']))<120)){
			return FIReturn("请勿频繁发送短信验证!");
		}
		$code = FISendSMS($phoneNumber,$content);
		$data = array();
		$data['smsSrc'] = $smsSrc;
		$data['smsUserId'] = $userId;
		$data['smsPhoneNumber'] = $phoneNumber;
		$data['smsContent'] = $content;
		$data['smsReturnCode'] = $code;
		$data['smsCode'] = $verfyCode;
		$data['smsIP'] = $ip;
		$data['smsFunc'] = $smsFunc;
		$data['createTime'] = date('Y-m-d H:i:s');
		$this->data($data)->save();
		if(intval($code)>0){
			return FIReturn("短信发送成功!",1);
		}else{
			return FIReturn("短信发送失败!");
		}
	}
}
