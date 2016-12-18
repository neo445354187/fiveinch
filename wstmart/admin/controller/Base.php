<?php
namespace wstmart\admin\controller;

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

    public function getVerify()
    {
        WSTVerify();
    }

    public function uploadPic()
    {
        return WSTUploadPic(1);
    }

    /**
     * 编辑器上传文件
     */
    public function editorUpload()
    {
        return WSTEditUpload(1);
    }
}
