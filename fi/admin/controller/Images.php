<?php
namespace fi\admin\controller;

use fi\admin\model\Images as M;

/**
 * 图片空间控制器
 */
class Images extends Base
{
    /**
     * 进入主页面
     */
    public function index()
    {
        return $this->fetch();
    }
    /**
     * 获取概况
     * 后台商城消息 编辑器中的图片只记录上传图片容量  删除相关数据时无法标记图片为已删除状态
     */
    public function summary()
    {
        $m    = new M();
        $data = $m->summary();
        return FIReturn("", 1, $data);
    }
    /**
     * 进入列表页面
     */
    public function lists()
    {
        $datas = model('Datas')->listQuery(3);
        $this->assign('datas', $datas);
        $this->assign('keyword', input('get.keyword'));
        return $this->fetch('list');
    }
    /**
     * 获取分页
     */
    public function pageQuery()
    {
        $m = new M();
        return $m->pageQuery();
    }
    /**
     * 检测图片信息
     */
    public function checkImages()
    {
        $img_path       = input('get.img_path');
        $m             = FIConf('CONF.fiMobileImgSuffix');
        $img_path       = str_replace($m . '.', '.', $img_path);
        $img_path       = str_replace($m . '_thumb.', '.', $img_path);
        $img_path       = str_replace('_thumb.', '.', $img_path);
        $img_path_thumb = str_replace('.', '_thumb.', $img_path);
        $mimg          = '';
        $mimg_thumb    = '';
        if ($m != '') {
            $mimg       = str_replace('.', $m . '.', $img_path);
            $mimg_thumb = str_replace('.', $m . '_thumb.', $img_path);
        }
        $data['imgpath']    = $img_path;
        $data['img']        = file_exists(FIRootPath() . "/" . $img_path) ? true : false;
        $data['thumb']      = file_exists(FIRootPath() . "/" . $img_path_thumb) ? true : false;
        $data['thumbpath']  = $img_path_thumb;
        $data['mimg']       = file_exists(FIRootPath() . "/" . $mimg) ? true : false;
        $data['mimgpath']   = $mimg;
        $data['mthumb']     = file_exists(FIRootPath() . "/" . $mimg_thumb) ? true : false;
        $data['mthumbpath'] = $mimg_thumb;
        return $this->fetch('view', $data);
    }
    /**
     * 删除
     */
    public function del()
    {
        $m = new M();
        return $m->del();
    }
}
