<?php

namespace fi\home\controller;

/**
 * 默认控制器
 */
class Index extends Base {

    public function index() {
        // echo microtime(true).'<br/>';
        // (new \fi\common\model\Redundancy)->add(4);
        // echo microtime(true);
        // var_dump(\think\Db::name('Redundancy'));
        // die;//debug

        $categorys = model('GoodsCats')->getFloors(); //获取首页楼层信息
        $this->assign('floors', $categorys);
        $this->assign('hideCategory', 1);
        $this->assign('method', 'index');
        return $this->fetch('default/index');
    }

    /**
     * 保存目录ID
     */
    public function getMenuSession() {
        $menu_id = input("post.menu_id");
        $menu_type = session('FI_USER.loginTarget');
        session('FI_MENUID3' . $menu_type, $menu_id);
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
        $menu_id = (int) input("post.menu_id");
        $menu_type = ((int) input("post.menu_type") == 1) ? 1 : 0;
        session('FI_MENUID3' . $menu_type, $menu_id);
    }

}
