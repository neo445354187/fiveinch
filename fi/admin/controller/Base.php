<?php
namespace fi\admin\controller;

/**
 * 基础控制器
 */
use think\Controller;

class Base extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->assign("v", FIConf('CONF.fiVersion'));
    }

    public function getVerify()
    {
        FIVerify();
    }

    public function uploadPic()
    {
        return FIUploadPic(1);
    }

    /**
     * 编辑器上传文件
     */
    public function editorUpload()
    {
        return FIEditUpload(1);
    }
}
