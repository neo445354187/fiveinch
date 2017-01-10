<?php
namespace fi\common\model;
use think\Db;
/**
 * 订单投诉类
 */
class OrderComplains extends Base{
	 /**
	  * 获取用户投诉列表
	  */
	public function queryUserComplainByPage(){
		$user_id = (int)session('FI_USER.user_id');
		$order_no = (int)Input('order_no');

		$where['o.user_id'] = $user_id;
		if($order_no>0){
			$where['o.order_no'] = ['like',"%$order_no%"];
		}
		$rs = $this->alias('oc')
				   ->field('oc.complain_id,o.order_id,o.order_no,s.shop_id,s.shop_name,oc.complain_content,oc.complain_status,oc.complain_time')
				   ->join('__SHOPS__ s','oc.respond_target_id=s.shop_id','left')
				   ->join('__ORDERS__ o','oc.order_id=o.order_id and o.status=1','inner')
				   ->order('oc.complain_id desc')
				   ->where($where)
				   ->paginate()->toArray();
		foreach($rs['Rows'] as $k=>$v){
			if($v['complain_status']==0){
				$rs['Rows'][$k]['complain_status'] = '等待处理';
			}elseif($v['complain_status']==1){
				$rs['Rows'][$k]['complain_status'] = '等待被投诉方回应';
			}elseif($v['complain_status']==2 || $v['complain_status']==3 ){
				$rs['Rows'][$k]['complain_status'] = '等待仲裁';
			}elseif($v['complain_status']==4){
				$rs['Rows'][$k]['complain_status'] = '已仲裁';
			}
		}
		if($rs !== false){
			return FIReturn('',1,$rs);
		}else{
			return FIReturn($this->getError(),-1);
		}
	}
	/**
	 * 获取订单信息
	 */
	public function getOrderInfo(){
		$user_id = (int)session('FI_USER.user_id');
		$order_id = (int)Input('order_id');

		//判断是否提交过投诉
		$rs = $this->alreadyComplain($order_id,$user_id);
		$data = array('complain_status'=>1);
		if($rs['complain_id']==''){
			$where['o.order_id'] = $order_id;
			$where['o.user_id'] = $user_id;
			//获取订单信息
			$order = db('orders')->alias('o')
			 						 ->field('o.real_total_money,o.order_no,o.order_id,o.create_time,o.deliver_money,s.shop_name,s.shop_id')
									 ->join('__SHOPS__ s','o.shop_id=s.shop_id','left')
									 ->where($where)
									 ->find();
			if($order){
				//获取相关商品
			    $goods = $this->getOrderGoods($order_id);
				$order["goodsList"] = $goods;
			}
			$data['order'] = $order;
			$data['complain_status'] = 0;
		}
		
        return $data;
	}
	// 判断是否已经投诉过
	public function alreadyComplain($order_id,$user_id){
		return $this->field('complain_id')->where("order_id=$order_id and complain_target_id=$user_id")->find();
	}
	//获取相关商品
	public function getOrderGoods($order_id){
	  return db('goods')->alias('g')
						->field('og.order_id, og.goods_id ,g.goods_sn, og.goods_name , og.goods_price shop_price,og.goods_img')
						->join('__ORDER_GOODS__ og','g.goods_id = og.goods_id','inner')
						->where("og.order_id=$order_id")
						->select();
	}

	/**
	 * 保存订单投诉信息
	 */
	public function saveComplain(){

		$user_id = (int)session('FI_USER.user_id');
		$data['order_id'] = (int)input('order_id');
        //判断订单是否该用户的
		$order = db('orders')->field('order_id,shop_id')->where("user_id=$user_id")->find($data['order_id']);
		if(!$order){
			return FIReturn('无效的订单信息',-1);
		}

		//判断是否提交过投诉
		$rs = $this->alreadyComplain($data['order_id'],$user_id);

		if((int)$rs['complain_id']>0){
			return FIReturn("该订单已进行了投诉,请勿重提提交投诉信息",-1);
		}
		Db::startTrans();
		try{
			$data['complain_target_id'] = $user_id;
			$data['respond_target_id'] = $order['shop_id'];
			$data['complain_status'] = 0;
			$data['complain_time'] = date('Y-m-d H:i:s');
			$data['complain_annex'] = input('complain_annex');
			$data['complain_content'] = input('complain_content');
			$rs = $this->validate('OrderComplains.add')->save($data);
			if($rs !==false){
				FIUseImages(0, $this->complain_id, $data['complain_annex']);
				Db::commit();
				return FIReturn('',1);
			}
		}catch (\Exception $e) {
		    Db::rollback();
	    }
	    return FIReturn('投诉失败',-1);
	}

	/**
	 * 获取投诉详情
	 */
	public function getComplainDetail($user_type = 0){
		$user_id = (int)session('FI_USER.user_id');
		$shop_id = (int)session('FI_USER.shop_id');
		$id = (int)Input('id');
		if($user_type==0){
			$where['complain_target_id']=$user_id;
		}else{
			$where['need_respond'] = 1;
			$where['respond_target_id'] = $shop_id;
		}

		//获取订单信息
		$where['complain_id'] = $id;
		$rs = $this->alias('oc')
				   ->field('oc.*,o.real_total_money,o.order_no,o.order_id,o.create_time,o.deliver_money,s.shop_name,s.shop_id')
				   ->join('__ORDERS__ o','oc.order_id=o.order_id','inner')
				   ->join('__SHOPS__ s','o.shop_id=s.shop_id')
				   ->where($where)->find();
		if($rs){
			if($rs['complain_annex']!='')$rs['complain_annex'] = explode(',',$rs['complain_annex']);
			if($rs['respond_annex']!='')$rs['respond_annex'] = explode(',',$rs['respond_annex']);

			//获取相关商品
			$goods = $this->getOrderGoods($rs['order_id']);
			$rs["goodsList"] = $goods;
		}
        return $rs;
	}






	/************************************* 商家 *********************************************/
	/**
	  * 获取商家被投诉列表
	  */
	public function queryShopComplainByPage(){
		$shop_id = (int)session('FI_USER.shop_id');
		$order_no = (int)Input('order_no');
		if($order_no!=''){
			$where['o.order_no'] = ['like',"%$order_no%"];
		}
		$where['oc.need_respond'] = 1;
		$where['o.status'] = 1;
		$where['oc.respond_target_id'] = $shop_id;
		$rs = $this->alias('oc')
				   ->field('oc.complain_id,o.order_id,o.order_no,u.user_name,u.login_name,oc.complain_content,oc.complain_status,oc.complain_time')
				   ->join('__USERS__ u','oc.complain_target_id=u.user_id','left')
				   ->join('__ORDERS__ o','oc.order_id=o.order_id')
				   ->where($where)
				   ->order('oc.complain_id desc')
				   ->paginate()
				   ->toArray();
		foreach($rs['Rows'] as $k=>$v){
			if($v['complain_status']==0){
				$rs['Rows'][$k]['complain_status'] = '等待处理';
			}elseif($v['complain_status']==1){
				$rs['Rows'][$k]['complain_status'] = '等待被投诉方回应';
				$rs['Rows'][$k]['needReply'] = 1;
			}elseif($v['complain_status']==2 || $v['complain_status']==3 ){
				$rs['Rows'][$k]['complain_status'] = '等待仲裁';
			}elseif($v['complain_status']==4){
				$rs['Rows'][$k]['complain_status'] = '已仲裁';
			}
		}
		if($rs!==false){
			return FIReturn('',1,$rs);
		}else{
			return FIReturn($this->getError,-1);
		}
	}
	/**
	 * 保存订单应诉信息
	 */
	public function saveRespond(){
		$shop_id = (int)session('FI_USER.shop_id');
		$complain_id = (int)Input('complain_id');
		//判断是否提交过应诉和是否有效的投诉信息
		$rs = $this->field('need_respond,complain_status')->where("complain_id=$complain_id AND respond_target_id=$shop_id")->find();
        if((int)$rs['need_respond']!=1){
			return FIReturn('无效的投诉信息',-1);
		}
		if((int)$rs['complain_status']!=1){
			return FIReturn('该投诉订单已进行了应诉,请勿重复提交应诉信息',-1);
		}
		Db::startTrans();
		try{
			$data['complain_status'] = 3;
			$data['respond_time'] = date('Y-m-d H:i:s');
			$data['respond_annex'] = Input('respond_annex');
			$data['respond_content'] = Input('respond_content');
			$rs = $this->validate('OrderComplains.respond')->where('complain_id='.$complain_id)->update($data);
			if($rs !==false){
				FIUseImages(0, $complain_id, $data['respond_annex']);
				Db::commit();
				return FIReturn('应诉成功',1);
			}
		}catch (\Exception $e) {
		    Db::rollback();
	    }
	    return FIReturn('投诉失败',-1);


	}
	
	
}
