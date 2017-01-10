<?php
namespace fi\admin\controller;
use fi\admin\model\Orders as M;
/**
 * 订单控制器
 */
class Orders extends Base{
	/**
	 * 订单列表
	 */
    public function index(){
    	$areaList = model('areas')->listQuery(0); 
    	$this->assign("areaList",$areaList);
    	return $this->fetch("list");
    }
    /**
     * 获取分页
     */
    public function pageQuery(){
        $m = new M();
        return $m->pageQuery((int)input('order_status',10000));
    }
    /**
     * 退款列表
     */
    public function refund(){
    	$areaList = model('areas')->listQuery(0); 
    	$this->assign("areaList",$areaList);
    	return $this->fetch("list_refund");
    }
    public function refundPageQuery(){
        $m = new M();
        return $m->refundPageQuery();
    }
    /**
     * 跳去退款界面
     */
    public function toRefund(){
    	$m = new M();
    	$object = $m->getInfoByRefund();
    	$this->assign("object",$object);
    	return $this->fetch("box_refund");
    }
    /**
     * 退款
     */
    public function orderRefund(){
    	$m = new M();
        return $m->orderRefund();
    }
   /**
    * 获取订单详情
    */
    public function view(){
        $m = new M();
        $rs = $m->getByView(Input("id/d",0));
        $this->assign("object",$rs);
        return $this->fetch("view");
    }
}
