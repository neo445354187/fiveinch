<?php
namespace fi\home\model;
use think\Db;
/**
 * 支付管理业务处理
 */
class Payments extends Base{
	/**
	 * 获取支付方式种类
	 */
	public function getByGroup(){
		$payments = ['0'=>[],'1'=>[]];
		$rs = $this->where(['enabled'=>1])->order('pay_order asc')->select();
		foreach ($rs as $key =>$v){
			if($v['pay_config']!='')$v['pay_config'] = json_decode($v['pay_config'], true);
			$payments[$v['is_online']][] = $v;
		}
		return $payments;
	}
	

	
	
	
	/**
	 * 获取支付信息
	 * @return unknown
	 */
	public function getPayment($pay_code){
		$payment = $this->where("enabled=1 AND pay_code='$pay_code' AND is_online=1")->find();
		$pay_config = json_decode($payment["pay_config"]) ;
		foreach ($pay_config as $key => $value) {
			$payment[$key] = $value;
		}
		return $payment;
	}
	
	/**
	 * 获取支付订单信息
	 */
	public function getPayOrders ($obj){
		$user_id = (int)$obj["user_id"];
		$order_id = $obj["order_id"];
		$isBatch = (int)$obj["isBatch"];
		$need_pay = 0;
		$where = ["user_id"=>$user_id,"status"=>1,"order_status"=>-2,"is_pay"=>0,"pay_type"=>1,"need_pay"=>[">",0]];
		if($isBatch==1){
			$where['order_unique'] = $order_id;
		}else{
			$where['order_id'] = $order_id;
		}
		return model('orders')->where($where)->sum('need_pay');
	}
	
	/**
	 * 完成支付订单
	 */
	public function complatePay ($obj){
		$trade_no = $obj["trade_no"];
		$isBatch = (int)$obj["isBatch"];
		$order_id = $obj["out_trade_no"];
		$user_id = (int)$obj["user_id"];
		$pay_from = (int)$obj["pay_from"];
		$payMoney = (float)$obj["total_fee"];
		
		if($pay_from>0){
			$cnt = model('orders')
						->where(['pay_from'=>$pay_from,"user_id"=>$user_id,"trade_no"=>$trade_no])
						->count();
			if($cnt>0){
				return FIReturn('订单已支付',-1);
			}
		}
		$where = ["user_id"=>$user_id,"status"=>1,"order_status"=>-2,"is_pay"=>0,"pay_type"=>1,"need_pay"=>[">",0]];
		if($isBatch==1){
			$where['order_unique'] = $order_id;
		}else{
			$where['order_id'] = $order_id;
		}
		$need_pay = model('orders')->where($where)->sum('need_pay');
		
		if($need_pay>$payMoney){
			return FIReturn('支付金额不正确',-1);
		}
		Db::startTrans();
		try{
			$data = array();
			$data["need_pay"] = 0;
			$data["is_pay"] = 1;
			$data["order_status"] = 0;
			$data["trade_no"] = $trade_no;
			$data["pay_from"] = $pay_from;
			$rs = model('orders')->where($where)->update($data);
			
			if($need_pay>0 && false != $rs){
				$where = ["o.user_id"=>$user_id];
				if($isBatch==1){
					$where["order_unique"] = $order_id;
				}else{
					$where["order_id"] = $order_id;
				}
				$list = Db::table('__ORDERS__')->alias('o')->join('__SHOPS__ s','o.shop_id=s.shop_id ','inner')
					          ->where($where)->field('order_id,order_no,s.user_id')
					          ->select();
				if(!empty($list)){
					foreach ($list as $key =>$v){
						$order_id = $v["order_id"];
						//新增订单日志
						$logOrder = [];
						$logOrder['order_id'] = $order_id;
						$logOrder['order_status'] = 0;
						$logOrder['log_content'] = "订单已支付,下单成功";
						$logOrder['log_user_id'] = $user_id;
						$logOrder['log_type'] = 0;
						$logOrder['log_time'] = date('Y-m-d H:i:s');
						Db::name('log_orders')->insert($logOrder);
						//发送一条商家信息
						$msg_content = "订单【".$v['order_no']."】用户已支付完成，请尽快发货哦~";
						FISendMsg($v["user_id"],$msg_content,['from'=>1,'data_id'=>$order_id]);
					}
				}
			}
			Db::commit();
			return FIReturn('支付成功',1);
		}catch (\Exception $e) {
			Db::rollback();
			return FIReturn('操作失败',-1);
		}
	}
	
}
