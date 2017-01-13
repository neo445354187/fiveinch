<?php
namespace fi\home\controller;
use fi\common\model\GoodsAppraises as M;
/**
 * 评价控制器
 */
class GoodsAppraises extends Base{
	/**
	* 获取评价列表 商家
	*/
	public function index(){
		return $this->fetch('default/shops/goodsappraises/list');
	}
	/**
	* 获取评价列表 用户
	*/
	public function myAppraise(){
		return $this->fetch('default/users/orders/appraise_manage');
	}
	// 获取评价列表 商家
	public function queryByPage(){
		$m = new M();
		return $m->queryByPage();
	}
	// 获取评价列表 用户
	public function userAppraise(){
		$m = new M();
		return $m->userAppraise();
	}
	/**
	* 添加评价
	*/
	public function add(){
		$m = new M();
		$rs = $m->add();
		return $rs;

	}
	/**
	* 根据商品id取评论
	*/
	public function getById(){
		return json_encode((new M())->getById());//debug 暂未知道前端获取什么类型
	}

	/**
	* 商家回复评价
	*/
	public function shop_reply(){
		$m = new M();
		return $m->shop_reply();
	}
}
