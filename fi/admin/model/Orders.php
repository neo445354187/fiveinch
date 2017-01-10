<?php
namespace fi\admin\model;
use think\Db;
/**
 * 订单业务处理类
 */
class Orders extends Base{
	/**
	 * 获取用户订单列表
	 */
	public function pageQuery($order_status = 10000,$is_appraise = -1){
		$where = ['o.status'=>1];
		if($order_status!=10000){
			$where['order_status'] = $order_status;
		}
		$order_no = input('order_no');
		$shop_name = input('shop_name');
		$pay_type = (int)input('pay_type',-1);
		$deliver_type = (int)input('deliver_type',-1);
		if($is_appraise!=-1)$where['is_appraise'] = $is_appraise;
		if($order_no!='')$where['order_no'] = ['like','%'.$order_no.'%'];
		if($shop_name!='')$where['shop_name|shop_sn'] = ['like','%'.$shop_name.'%'];

		$area_id1 = (int)input('area_id1');

		if($area_id1>0){
			$where['s.area_id_path'] = ['like',"$area_id1%"];
			$area_id2 = (int)input("area_id1_".$area_id1);
			if($area_id2>0)$where['s.area_id_path'] = ['like',$area_id1."_"."$area_id2%"];
			$area_id3 = (int)input("area_id1_".$area_id1."_".$area_id2);
			if($area_id3>0)$where['s.area_id'] = $area_id3;
		}

		if($deliver_type!=-1)$where['o.deliver_type'] = $deliver_type;
		if($pay_type!=-1)$where['o.pay_type'] = $pay_type;
		$page = $this->alias('o')->join('__SHOPS__ s','o.shop_id=s.shop_id','left')->where($where)
		     ->field('o.order_id,o.order_no,s.shop_name,s.shop_id,s.shop_qq,s.shop_wangwang,o.goods_money,o.total_money,o.real_total_money,
		              o.order_status,o.user_name,o.deliver_type,pay_type,pay_from,o.order_status,order_src,o.create_time')
			 ->order('o.create_time', 'desc')
			 ->paginate(input('pagesize/d'))->toArray();
	    if(count($page['Rows'])>0){
	    	 foreach ($page['Rows'] as $key => $v){
	    	 	 $page['Rows'][$key]['pay_type'] = FILangPayType($v['pay_type']);
	    	 	 $page['Rows'][$key]['deliver_type'] = FILangDeliverType($v['deliver_type']==1);
	    	 	 $page['Rows'][$key]['status'] = FILangOrderStatus($v['order_status']);
	    	 }
	    }
	    return $page;
	}
	
    /**
	 * 获取用户退款订单列表
	 */
	public function refundPageQuery(){
		$where = ['o.status'=>1];
		$where['order_status'] = ['in',[-1,-4]];
		$where['o.pay_type'] = 1;
		$order_no = input('order_no');
		$shop_name = input('shop_name');
		$deliver_type = (int)input('deliver_type',-1);
		$area_id1 = (int)input('area_id1');
		$area_id2 = (int)input('area_id2');
		$area_id3 = (int)input('area_id3');
		$is_refund = (int)input('is_refund',-1);
		if($order_no!='')$where['order_no'] = ['like','%'.$order_no.'%'];
		if($shop_name!='')$where['shop_name|shop_sn'] = ['like','%'.$shop_name.'%'];
		if($area_id1>0)$where['s.area_id1'] = $area_id1;
		if($area_id2>0)$where['s.area_id2'] = $area_id2;
		if($area_id3>0)$where['s.area_id3'] = $area_id3;
		if($deliver_type!=-1)$where['o.deliver_type'] = $deliver_type;
		if($is_refund!=-1)$where['o.is_refund'] = $is_refund;
		$page = $this->alias('o')->join('__SHOPS__ s','o.shop_id=s.shop_id','left')
		     ->join('__ORDER_REFUNDS__ orf ','o.order_id=orf.order_id','left') 
		     ->where($where)
		     ->field('o.order_id,o.order_no,s.shop_name,s.shop_id,s.shop_qq,s.shop_wangwang,o.goods_money,o.total_money,o.real_total_money,
		              o.order_status,o.user_name,o.deliver_type,pay_type,pay_from,o.order_status,order_src,orf.refund_remark,is_refund,o.create_time')
			 ->order('o.create_time', 'desc')
			 ->paginate(input('pagesize/d'))->toArray();
	    if(count($page['Rows'])>0){
	    	 foreach ($page['Rows'] as $key => $v){
	    	 	 $page['Rows'][$key]['pay_type'] = FILangPayType($v['pay_type']);
	    	 	 $page['Rows'][$key]['deliver_type'] = FILangDeliverType($v['deliver_type']==1);
	    	 	 $page['Rows'][$key]['status'] = FILangOrderStatus($v['order_status']);
	    	 }
	    }
	    return $page;
	}
	/**
	 * 获取退款资料
	 */
	public function getInfoByRefund(){
		return $this->where(['order_id'=>(int)input('get.id'),'is_refund'=>0,'order_status'=>['in',[-1,-4]]])
		         ->field('order_no,order_id,goods_money,total_money,real_total_money,deliver_money,pay_type,pay_from,trade_no')
		         ->find();
	}
	/**
	 * 退款
	 */
	public function orderRefund(){
		$id = (int)input('post.id');
		$content = input('post.content');
		if($id==0 || $content=='')return FIReturn("操作失败!");
		$order = $this->where(['order_id'=>(int)input('post.id'),'is_refund'=>0,'order_status'=>['in',[-1,-4]]])
		         ->field('user_id,order_no,order_id,goods_money,total_money,real_total_money,deliver_money,pay_type,pay_from,trade_no')
		         ->find();
		if(empty($order))return FIReturn("该订单不存在或已退款!");
		Db::startTrans();
        try{
			$order->is_refund = 1;
			$order->save();
			$data = [];
			$data['order_id'] = $id;
			$data['refund_remark'] = $content;
			$data['refund_time'] = date('Y-m-d H:i:s');
			$rs = Db::table('__ORDER_REFUNDS__')->insert($data);
			if(false !== $rs){
				//发送一条用户信息
				FISendMsg($order['user_id'],"您的订单【".$order['order_no']."】已退款，退款备注：".$content,['from'=>1,'data_id'=>$id]);
				Db::commit();
				return FIReturn("操作成功",1); 
			}
        }catch (\Exception $e) {
            Db::rollback();
        }
		return FIReturn("操作失败，请刷新后再重试"); 
	}
	
	
	/**
	 * 获取订单详情
	 */
	public function getByView($order_id){
		$orders = $this->alias('o')->join('__EXPRESS__ e','o.express_id=e.express_id','left')
		               ->join('__ORDER_REFUNDS__ orf ','o.order_id=orf.order_id','left')
		               ->join('__SHOPS__ s','o.shop_id=s.shop_id','left')
		               ->where('o.status=1 and o.order_id='.$order_id)
		               ->field('o.*,e.express_name,s.shop_name,s.shop_qq,s.shop_wangwang,orf.refund_remark,orf.refund_time')->find();
		if(empty($orders))return FIReturn("无效的订单信息");
		//获取订单信息
		$orders['log'] = Db::name('log_orders')->where('order_id',$order_id)->order('log_id asc')->select();
		//获取订单商品
		$orders['goods'] = Db::name('order_goods')->where('order_id',$order_id)->order('id asc')->select();
		return $orders;
	}
}
