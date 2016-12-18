<?php
namespace wstmart\home\controller;
use wstmart\common\model\Carts as M;
/**
 * 购物车控制器
 */
class Carts extends Base{
    /**
    * 查看商城消息
    */
	public function addCart(){
		$m = new M();
		$rs = $m->addCart();
		return $rs;
	}
	/**
	 * 查看购物车列表
	 */
	public function index(){
		$m = new M();
		$carts = $m->getCarts(false);
		$this->assign('carts',$carts);
		return $this->fetch('default/carts');
	}
	/**
	 * 删除购物车里的商品
	 */
	public function delCart(){
		$m = new M();
		$rs= $m->delCart();
		return $rs;
	}
	/**
	 * 跳去购物车结算页面
	 */
    public function settlement(){
		$m = new M();
		//获取一个用户地址
		$userAddress = model('UserAddress')->getDefaultAddress();
		$this->assign('userAddress',$userAddress);
		//获取省份
		$areas = model('Areas')->listQuery();
		$this->assign('areaList',$areas);
		//获取支付方式
		$payments = model('Payments')->getByGroup();
		$this->assign('payments',$payments);
		//获取已选的购物车商品
		$carts = $m->getCarts(true);
		$this->assign('carts',$carts);
		return $this->fetch('default/settlement');
	}
	
	/**
	 * 计算运费和总商品价格
	 */
	public function getCartMoney(){
		$areaId = input('post.areaId2/d',-1);
		//获取已选的购物车商品
		$m = new M();
		$carts = $m->getCarts(true);
		$shopFreight = 0;
		$data = ['shops'=>[],'total'=>0,'status'=>1];
		foreach ($carts['carts'] as $key =>$v){
			$shopFreight = WSTOrderFreight($v['shopId'],$areaId);
			$data['shops'][$v['shopId']] = $shopFreight;
			$data['total'] = $v['goodsMoney'] + $shopFreight;
		}
		return $data;
	}
	/**
	 * 修改购物车商品状态
	 */
	public function changeCartGoods(){
		$m = new M();
		$rs = $m->changeCartGoods();
		return $rs;
	}
	/**
	 * 获取购物车商品
	 */
    public function getCart(){
		$m = new M();
		$carts = $m->getCarts(false);
		return WSTReturn("", 1,$carts);;
	}
	/**
	 * 获取购物车信息
	 */
	public function getCartInfo(){
		$m = new M();
		$rs = $m->getCartInfo();
		return WSTReturn("", 1,$rs);
	}
}
