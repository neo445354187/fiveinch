<?php
namespace fi\admin\controller;
use fi\admin\model\GoodsCats as M;
/**
 * 商品分类控制器
 */
class GoodsCats extends Base{
	
    public function index(){
    	return $this->fetch("list");
    }
    
    /**
     * 获取分页
     */
    public function pageQuery(){
    	$m = new M();
    	return $m->pageQuery();
    }
    /**
     * 获取列表
     */
    public function listQuery(){
    	$m = new M();
    	$rs = $m->listQuery(input('parent_id/d',0));
    	return FIReturn("", 1,$rs);
    }
    /**
     * 获取商品分类
     */
    public function get(){
    	$m = new M();
    	return $m->get((int)Input("post.id"));
    }
    
    /**
     * 设置是否推荐/不推荐
     */
    public function editiIsFloor(){
    	$m = new M();
    	return $m->editiIsFloor();
    }
       
    /**
     * 设置是否显示/隐藏
     */
    public function editiIsShow(){
    	$m = new M();
    	return $m->editiIsShow();
    }
    
    /**
     * 新增
     */
    public function add(){
    	$m = new M();
    	return $m->add();
    }
    
    /**
     * 编辑
     */
    public function edit(){
    	$m = new M();
    	return $m->edit();
    }
    
    /**
     * 删除
     */
    public function del(){
    	$m = new M();
    	return $m->del();
    }
}
