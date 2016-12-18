<?php
namespace wstmart\home\controller;

/**
 * 基础控制器
 */
use think\Controller;

class Base extends Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->assign("v", WSTConf('CONF.wstVersion'));
    }
    /**
     * 上传图片
     */
    public function uploadPic()
    {
        return WSTUploadPic(0);
    }
    /**
     * 编辑器上传文件
     */
    public function editorUpload()
    {
        return WSTEditUpload(0);
    }

    /**
     * 获取验证码
     */
    public function getVerify()
    {
        WSTVerify();
    }

}
