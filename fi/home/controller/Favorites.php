<?php

namespace fi\home\controller;

use fi\common\model\Favorites as M;

/**
 * 收藏控制器
 */
class Favorites extends Base {

    /**
     * 关注的商品
     */
    public function goods() {
        $data = (new M())->listGoodsQuery();
        return $this->fetch('default/users/favorites/list_goods');
    }

    /**
     * 关注的店铺
     */
    public function shops() {
        return $this->fetch('default/users/favorites/list_shops');
    }

    /**
     * 关注的商品列表
     */
    public function listGoodsQuery() {
        $m = new M();
        $data = $m->listGoodsQuery();
        return FIReturn("", 1, $data);
    }

    /**
     * 关注的店铺列表
     */
    public function listShopQuery() {
        $m = new M();
        $data = $m->listShopQuery();
        return FIReturn("", 1, $data);
    }

    /**
     * 取消关注
     */
    public function cancel() {
        $m = new M();
        $rs = $m->del();
        return $rs;
    }

    /**
     * 增加关注
     */
    public function add() {
        $m = new M();
        $rs = $m->add();
        return $rs;
    }

}
