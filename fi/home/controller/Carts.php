<?php

namespace fi\home\controller;

use fi\common\model\Carts as M;

/**
 * 购物车控制器
 */
class Carts extends Base {

    /**
     * 查看商城消息
     */
    public function addCart() {
        $m = new M();
        $rs = $m->addCart();
        return $rs;
    }

    /**
     * 查看购物车列表
     */
    public function index() {
        $m = new M();
        $carts = $m->getCarts(false);
        $this->assign('carts', $carts);
        return $this->fetch('default/carts');
    }

    /**
     * 删除购物车里的商品
     */
    public function delCart() {
        $m = new M();
        $rs = $m->delCart();
        return $rs;
    }

    /**
     * 跳去购物车结算页面
     */
    public function settlement() {
        $m = new M();
        //获取一个用户地址
        $user_address = model('UserAddress')->getDefaultAddress();
        $this->assign('user_address', $user_address);
        //获取省份
        $areas = model('Areas')->listQuery();
        $this->assign('areaList', $areas);
        //获取支付方式
        $payments = model('Payments')->getByGroup();
        $this->assign('payments', $payments);
        //获取已选的购物车商品
        $carts = $m->getCarts(true);
        $this->assign('carts', $carts);
        return $this->fetch('default/settlement');
    }

    /**
     * 计算运费和总商品价格
     */
    public function getCartMoney() {
        $area_id = input('post.area_id2/d', -1);
        //获取已选的购物车商品
        $m = new M();
        $carts = $m->getCarts(true);
        $shopFreight = 0;
        $data = ['shops' => [], 'total' => 0, 'status' => 1];
        foreach ($carts['carts'] as $key => $v) {
            $shopFreight = FIOrderFreight($v['shop_id'], $area_id);
            $data['shops'][$v['shop_id']] = $shopFreight;
            $data['total'] = $v['goods_money'] + $shopFreight;
        }
        return $data;
    }

    /**
     * 修改购物车商品状态
     */
    public function changeCartGoods() {
        $m = new M();
        $rs = $m->changeCartGoods();
        return $rs;
    }

    /**
     * 获取购物车商品
     */
    public function getCart() {
        $m = new M();
        $carts = $m->getCarts(false);
        return FIReturn("", 1, $carts);
    }

    /**
     * 获取购物车信息
     */
    public function getCartInfo() {
        $m = new M();
        $rs = $m->getCartInfo();
        return FIReturn("", 1, $rs);
    }

}
