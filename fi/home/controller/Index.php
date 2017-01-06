<?php

namespace fi\home\controller;

/**
 * 默认控制器
 */
class Index extends Base {

    public function index() {
        echo microtime(true).'<br/>';
        (new \fi\common\model\Redundancy)->edit(1);
        echo microtime(true);
        // var_dump(\think\Db::name('Redundancy'));
        die;//debug

        $categorys = model('GoodsCats')->getFloors(); //获取首页楼层信息
        $this->assign('floors', $categorys);
        $this->assign('hideCategory', 1);
        return $this->fetch('default/index');
    }

    /**
     * 保存目录ID
     */
    public function getMenuSession() {
        $menuId = input("post.menuId");
        $menuType = session('FI_USER.loginTarget');
        session('FI_MENUID3' . $menuType, $menuId);
    }

    /**
     * 获取用户信息
     */
    public function getSysMessages() {
        $rs = model('Systems')->getSysMessages();
        return $rs;
    }

    /**
     * 定位菜单以及跳转页面
     */
    public function position() {
        $menuId = (int) input("post.menuId");
        $menuType = ((int) input("post.menuType") == 1) ? 1 : 0;
        session('FI_MENUID3' . $menuType, $menuId);
    }

}
