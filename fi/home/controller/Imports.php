<?php
namespace fi\home\controller;
use fi\home\model\Imports as M;
/**
 * 默认控制器
 */
class Imports extends Base{
	/**
	 * 数据导入首页
	 */
	public function index(){
		return $this->fetch('default/shops/import');
	}
	
    /**
     * 上传商品数据
     */
    public function importGoods(){
    	$rs = FIUploadFile();
		if(json_decode($rs)->status==1){
			$m = new M();
    	    $rss = $m->importGoods($rs);
    	    return $rss;
		}
    	return $rs;
    }
}
