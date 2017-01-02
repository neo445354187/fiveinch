<?php
namespace fi\home\controller;

use fi\common\model\Areas;
use think\Session;

/**
 * 基础控制器
 */

class Base extends \fi\common\controller\Base
{
    public function __construct()
    {
        parent::__construct();
        //判断是否已经通过ip获取的城市地址
        $location = Session::get('user_location');
        if (!empty($location['city'])) {
            $this->assign("city", explode('_', $location['city'])[1]);
        } else {
            $this->assign("city", (new Areas())->getLocation());
        }

        $this->assign("v", FIConf('CONF.fiVersion'));
    }
    /**
     * 上传图片
     */
    public function uploadPic()
    {
        return FIUploadPic(0);
    }
    /**
     * 编辑器上传文件
     */
    public function editorUpload()
    {
        return FIEditUpload(0);
    }

    /**
     * 获取验证码
     */
    public function getVerify()
    {
        FIVerify();
    }

}
