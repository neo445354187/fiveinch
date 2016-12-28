<?php
namespace fi\admin\controller;

use fi\admin\model\Index as M;
use fi\admin\model\Menus;
use fi\admin\model\Staffs;

/**
 * 首页控制器
 */
class Index extends Base
{
    /**
     * 跳去登录页
     */
    public function login()
    {
        return $this->fetch("/login");
    }

    public function index()
    {
        $m  = new Menus();
        $ms = $m->getMenus();
        $this->assign("menus", $ms);
        return $this->fetch("/index");
    }

    /**
     * 获取子菜单
     */
    public function getSubMenus()
    {
        $m = new Menus();
        return $m->getSubMenus((int) Input('post.id'));
    }

    /**
     * 登录验证
     */
    public function checkLogin()
    {
        $m = new Staffs();
        return $m->checkLogin();
    }

    /**
     * 退出系统
     */
    public function logout()
    {
        session('FI_STAFF', null);
        return FIReturn("", 1);
    }

    /**
     * 系统预览
     */
    public function main()
    {
        $m  = new M();
        $rs = $m->summary();
        $this->assign("object", $rs);
        return $this->fetch("/main");
    }

    /**
     * 获取用户权限
     */
    public function getGrants()
    {
        $rs = session('FI_STAFF');
        if (empty($rs)) {
            return FIReturn("您未登录，请先登录系统", -1);
        }

        $rs     = $rs['privileges'];
        $grants = [];
        foreach ($rs as $v) {
            $grants[$v] = true;
        }
        return FIReturn("权限加载成功", 1, $grants);
    }
    /**
     * 清除缓存
     */
    public function clearcache()
    {
        $m  = new M();
        $rs = $m->clearCache();
        if ($rs) {
            return FIReturn("清除成功!", 1);
        } else {
            return FIReturn("清除失败 !");
        }
    }

    /**
     * 获取最新版本提示 //后门待定
     */
    public function getVersion()
    {
        return true;
        $version = FIConf("CONF.fiVersion");
        $key     = FIConf("CONF.fiMd5");
        $license = FIConf("CONF.mallLicense");
        $host    = request()->root(true);
        $url     = base64_encode('version=' . $version . '&version_md5=' . $key . "&license=" . $license . "&host=" . $host);
        $content = file_get_contents('http://www.fi.net/index.php?m=Api&c=Download&a=getLastVersion&key=' . $url);
        $json    = json_decode($content, true);
        if ($json['version'] == $version) {
            $json['version'] = "same";
        }
        return $json;
    }

    /**
     * 输入授权码
     */
    public function enterLicense()
    {
        return $this->fetch("/enter_license");
    }
    /**
     * 验证授权码 后门待定
     */
    public function verifyLicense()
    {
        return true;
        $license = input('license');
        $host    = request()->root(true);
        $key     = base64_encode('host=' . request()->root(true) . '&license=' . $license);
        $content = file_get_contents('http://www.fi.net/index.php?m=Api&c=License&a=verifyLicense&key=' . $key);
        $json    = json_decode($content, true);
        $rs      = array('status' => 1);
        if (isset($json['status']) && $json['status'] == 1) {
            $m  = new M();
            $rs = $m->saveLicense();
        }
        $rs['license'] = $json;
        return $rs;
    }

}
