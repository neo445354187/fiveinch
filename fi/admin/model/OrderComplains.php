<?php
namespace fi\admin\model;
use think\Db;
/**
 * 订单投诉业务处理
 */
class OrderComplains extends Base{
	/**
	 * 获取订单投诉列表
	 */
	public function pageQuery(){
		$shop_name = Input('shop_name');
     	$order_no = Input('order_no');

     	$complain_status = (int)Input('complain_status',-1);
     	
     	// 搜素条件
     	$area_id1 = (int)input('area_id1');
		if($area_id1>0){
			$where['s.area_id_path'] = ['like',"$area_id1%"];
			$area_id2 = (int)input("area_id1_".$area_id1);
			if($area_id2>0)$where['s.area_id_path'] = ['like',$area_id1."_"."$area_id2%"];
			$area_id3 = (int)input("area_id1_".$area_id1."_".$area_id2);
			if($area_id3>0)$where['s.area_id'] = $area_id3;
		}

	 	if($complain_status>-1)$where['oc.complain_status']=$complain_status;
	 	if($order_no!='')$where['o.order_no']=['like',"%$order_no%"];


     	$where['o.status']=1;
		$rs = Db::table('__ORDERS__')->alias('o')
							  ->field('oc.complain_id,o.order_id,o.order_no,s.shop_name,u.user_name,u.login_name,oc.complain_time,oc.complain_status,oc.complain_type')
						      ->join('__SHOPS__ s','o.shop_id=s.shop_id','inner','left')
						      ->join('__USERS__ u','o.user_id=u.user_id','inner')
						      ->join('__ORDER_COMPLAINS__ oc','oc.order_id=o.order_id','inner')
						      ->where($where)
						      ->order('complain_id desc')
						      ->paginate()
						      ->toArray();
		return $rs;
	}

	/**
	 * 获取订单详细信息
	 */
	 public function getDetail(){
	 	$complain_id = (int)Input('cid');
	 	$data = $this->alias('oc')
	 				 ->field('oc.*,u.user_name,u.login_name')
	 				 ->join('__USERS__ u','oc.complain_target_id=u.user_id','inner')
	 				 ->where("oc.complain_id=$complain_id")
	 				 ->find();
	 	if($data){
	 		if($data['complain_annex']!='')$data['complain_annex'] = explode(',',$data['complain_annex']);
	 		if($data['respond_annex']!='')$data['respond_annex'] = explode(',',$data['respond_annex']);
			$data['user_name'] = ($data['user_name']=='')?$data['login_name']:$data['user_name'];
		 	$rs = Db::table('__ORDERS__')->alias('o')
		 					  ->field('o.order_status,o.area_id,o.user_address,o.order_no,o.user_name,s.shop_name,o.user_address')
		 					  ->join('__SHOPS__ s','o.shop_id=s.shop_id','left')
		 					  ->where(['o.status'=>1,
		 					  		   'o.order_id'=>$data['order_id']])
		 					  ->find();
			//获取日志信息
			$rs['log'] = Db::table('__LOG_ORDERS__')->alias('lo')
										  ->field('lo.*,u.login_name,u.user_type,s.shop_name')
									      ->join('__USERS__ u','lo.log_user_id=u.user_id','left')
									      ->join('__SHOPS__ s','u.user_type!=0 and s.user_id=u.user_id','left')
									      ->where(['order_id'=>$data['order_id']])
									      ->select();
			//获取相关商品
			$rs['goodslist'] = Db::name('order_goods')->where(['order_id'=>$data['order_id']])->select();
			$data['order'] = $rs;
	 	}
		return $data;
	 }

	 /**
	  * 转交给应诉人应诉
	  */
	 public function deliverRespond(){
	 	$id = (int)Input('id');
	 	if($id==0){
	 		return FIReturn('无效的投诉信息',-1);
	 	}
	 	//判断是否已经处理过了
	 	$rs = $this->alias('oc')
	 			   ->field('oc.complain_status,oc.respond_target_id,o.order_no,s.user_id')
	 			   ->join('__ORDERS__ o','oc.order_id=o.order_id','inner')
	 			   ->join('__SHOPS__ s','o.shop_id = s.shop_id','left')
	 			   ->where("complain_id=$id")
	 			   ->find();
	 	if($rs['complain_status']==0){
	 		$data = array();
	 		$data['need_respond'] = 1;
	 		$data['complain_status'] = 1;
	 		$data['deliver_respond_time'] = date('Y-m-d H:i:s');
	 		Db::startTrans();
		    try{
		 	    $ers = $this->where('complain_id='.$id)->update($data);
		 	    if($ers!==false){
			 	    //发站内信息提醒
		 	    	FISendMsg($rs['user_id'],"您有新的被投诉订单【".$rs['order_no']."】，请及时回应以免影响您的店铺评分。",['from'=>3,'data_id'=>$id]);
					Db::commit();
					return FIReturn('操作成功',1);
		 	    }
		    }catch (\Exception $e) {
	            Db::rollback();
	            return FIReturn('操作失败',-1);
	        }
	 	}else{
	 	    return FIReturn('操作失败，该投诉状态已发生改变，请刷新后重试!',-1);
	 	}
	 	return $rd;
	 }

	 /**
	  * 仲裁
	  */
	 public function finalHandle(){
	 	$rd = array('status'=>-1,'msg'=>'无效的投诉信息');
	 	$complain_id = (int)Input('cid');
	 	if($complain_id==0){
	 		return FIReturn('无效的投诉信息',-1);
	 	}
	 	if(!in_array((int)Input("order_status",0),[0,-4,-5])){
	 		return FIReturn('无效的订单处理结果',-1);
	 	}
	 	//判断是否已经处理过了
	 	$rs = $this->alias('oc')
	 			   ->field('oc.complain_status,s.user_id shopUserId,o.shop_id,o.user_id,o.order_no,o.order_id,o.order_status,o.order_score,oc.need_respond')
	 			   ->join('__ORDERS__ o','oc.order_id=o.order_id','inner')
	 			   ->join('__SHOPS__ s','o.shop_id=s.shop_id','left')
	 			   ->where("complain_id=$complain_id")
	 			   ->find();
	 	if($rs['complain_status']!=4){
	 		$data = array();
	 		$data['final_handle_staff_id'] = session('FI_STAFF.staff_id');
	 		$data['complain_status'] = 4;
	 		$data['final_result'] = Input('final_result');
	 		$data['final_result_time'] = date('Y-m-d H:i:s');
	 		Db::startTrans();
		    try{
	 	        $ers = $this->where('complain_id='.$complain_id)->update($data);
	 	        if($ers!==false){
	 	        	//需要卖家回应的话则给卖家也一条消息
		 	    	if($rs['need_respond']==1){
		 	    		//发站内商家信息提醒
		 	    		FISendMsg($rs['shopUserId'],"您的被投诉订单【".$rs['order_no']."】已仲裁，请查看订单投诉详情。",['from'=>3,'data_id'=>$complain_id]);
		 	    	}
					//发站内用户信息提醒
		 	    	FISendMsg($rs['user_id'],"您的订单投诉【".$rs['order_no']."】已仲裁，请查看订单投诉详情。",['from'=>3,'data_id'=>$complain_id]);
                    //处理订单状态
					$order_id = $rs['order_id'];
					$user_id = $rs['user_id'];
					$order_status = (int)Input("order_status",0);
                    //增加订单日志
                    $complainTxt = '';
                    if($order_status==0){
                    	$complainTxt = '订单仲裁结果：维持现状';
                    }else{
                    	$complainTxt = ($order_status==-4)?"订单仲裁结果：同意用户拒收":"订单仲裁结果：不同意用户拒收";
                    }
					$data = array();
					$data["order_id"] = $order_id;
					$data["order_status"] = $order_status;
					$data["log_content"] = ($order_status==-4)?"订单仲裁结果：同意用户拒收":"订单仲裁结果：不同意用户拒收";
					$data["log_user_id"] = $rs['user_id'];
					$data["log_type"] = 0;
					$data["log_time"] = date('Y-m-d H:i:s');
					Db::name('log_orders')->insert($data);
					//根据仲裁结果，修改订单状态
					if($order_status!=0){
					    model('orders')->where("order_id=$order_id")->setField('order_status',$order_status);
					}
					Db::commit();
					return FIReturn('操作成功',1);
	 	        }
	 	    }catch(\Exception $e){
	 	    	Db::rollback();
	            return FIReturn('操作失败',-1);
	 	    }
	 	}else{
	 	    return FIReturn('操作失败，该投诉状态已发生改变，请刷新后重试!',-1);
	 	}

	 }
}
