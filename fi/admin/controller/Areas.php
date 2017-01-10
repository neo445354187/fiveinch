<?php
namespace fi\admin\controller;
use fi\admin\model\Areas as M;
/**
 * 地区控制器
 */
class Areas extends Base{
	
    public function index(){
    	$m = new M();
    	$pArea=array('area_id'=>0,'parent_id'=>0);
    	$parent_id = Input("get.parent_id/d",0);
    	if($parent_id>0){
    		$pArea = $m->getFieldsById($parent_id,['area_name,area_id,parent_id']);
    	}
    	$this->assign("pArea",$pArea);
    	return $this->fetch("list");
    }
    
    /**
     * 获取分页
     */
    public function pageQuery(){
    	$m = new M();
    	$rs = $m->pageQuery();
    	return $rs;
    }
    
    /**
     * 设置是否显示/隐藏
     */
    public function editiIsShow(){
    	$m = new M();
    	$rs = $m->editiIsShow();
    	return $rs;
    }
    
    /**
     * 获取地区
     */
    public function get(){
    	$m = new M();
    	$rs = $m->getById((int)Input("post.id"));
    	return $rs;
    }
    
    /**
     * 排序字母
     */
    public function letterObtain(){
    	$m = new M();
    	$rs = $m->letterObtain();
    	return $rs;
    }
    
    /**
     * 新增
     */
    public function add(){
    	$m = new M();
    	$rs = $m->add();
    	return $rs;
    }
    
    /**
     * 编辑
     */
    public function edit(){
    	$m = new M();
    	$rs = $m->edit();
    	return $rs;
    }
    
    /**
     * 删除
     */
    public function del(){
    	$m = new M();
    	$rs = $m->del();
    	return $rs;
    }
    
    /**
     * 列表查询
     */
    public function listQuery(){
    	$m = new M();
    	$list = $m->listQuery(Input("post.parent_id/d",0));
    	return FIReturn("", 1,$list);
    }
}
