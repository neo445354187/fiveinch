<?php

namespace fi\home\controller;

use fi\common\model\GoodsCats;
use fi\home\model\Goods;
use fi\home\model\Shops as M;

/**
 * 门店控制器
 */
class Shops extends Base {

    /**
     * 商家登录
     */
    public function login() {
        $USER = session('FI_USER');
        if (!empty($USER) && isset($USER['shop_id'])) {
            $this->redirect("shops/index");
        }
        $login_name = cookie("login_name");
        if (!empty($login_name)) {
            $this->assign('login_name', cookie("login_name"));
        } else {
            $this->assign('login_name', '');
        }
        return $this->fetch('default/shop_login');
    }

    /**
     * 商家中心
     */
    public function index() {
        session('FI_MENID1', null);
        session('FI_MENUID31', null);
        $s = new M();
        $data = $s->getShopSummary((int) session('FI_USER.shop_id'));
        $this->assign('data', $data);
        return $this->fetch('default/shops/index');
    }

    /**
     * 店铺街
     */
    public function shopStreet() {
        $g = new GoodsCats();
        $goodsCats = $g->listQuery(0);
        $this->assign('goodscats', $goodsCats);
        //店铺街列表
        $s = new M();
        $pagesize = 10;
        $selectedId = input("get.id/d");
        $this->assign('selectedId', $selectedId);
        $list = $s->pageQuery($pagesize);
        $this->assign('list', $list);
        $this->assign('keyword', input('keyword'));
        $this->assign('keytype', 1);
        return $this->fetch('default/shop_street');
    }

    /**
     * 店铺详情
     */
    public function home() {
        $s = new M();
        $shop_id = (int) input("param.shop_id/d");
        $data['shop'] = $s->getShopInfo($shop_id);

        $ct1 = input("param.ct1/d", 0);
        $ct2 = input("param.ct2/d", 0);
        $goods_name = input("param.goods_name");
        if (($data['shop']['shop_id'] == 1 || $shop_id == 0) && $ct1 == 0 && !isset($goods_name)) {
            $this->redirect('home/shops/selfShop');
        }

        if (empty($data['shop'])) {
            return $this->fetch('default/error_lost');
        }

        $data['shopcats'] = $f = model('ShopCats', 'model')->getShopCats($shop_id);
        $g = new Goods();
        $data['list'] = $g->shopGoods($shop_id);
        $this->assign('msort', input("param.msort/d", 0)); //筛选条件
        $this->assign('mdesc', input("param.mdesc/d", 1)); //升降序
        $this->assign('sprice', input("param.sprice")); //价格范围
        $this->assign('eprice', input("param.eprice"));
        $this->assign('ct1', $ct1); //一级分类
        $this->assign('ct2', $ct2); //二级分类
        $this->assign('goods_name', urldecode($goods_name)); //搜索
        $this->assign('data', $data);
        return $this->fetch('default/shop_home');
    }

    /**
     * 查看店铺设置
     */
    public function info() {
        $s = new M();
        $object = $s->getByView((int) session('FI_USER.shop_id'));
        $this->assign('object', $object);
        return $this->fetch('default/shops/shops/view');
    }

    /**
     * 自营店铺
     */
    public function selfShop() {
        $s = new M();
        $data['shop'] = $s->getShopInfo(1);
        if (empty($data['shop'])) {
            return $this->fetch('default/error_lost');
        }

        $this->assign('selfShop', 1);
        $data['shopcats'] = model('ShopCats')->getShopCats(1);
        $this->assign('goods_name', urldecode(input("param.goods_name"))); //搜索
        // 店长推荐
        $data['rec'] = $s->getRecGoods('rec');
        // 热销商品
        $data['hot'] = $s->getRecGoods('hot');
        $this->assign('data', $data);
        return $this->fetch('default/shops/shops/self_shop');
    }

}
