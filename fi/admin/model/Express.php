<?php
namespace fi\admin\model;
/**
 * 快递业务处理
 */
class Express extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->where('status',1)->field('express_id,express_name')->order('express_id desc')->paginate(input('pagesize/d'));
	}
	public function getById($id){
		return $this->get(['express_id'=>$id,'status'=>1]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = ['express_name'=>input('post.express_name')];
		$result = $this->validate('Express.add')->allowField(['express_name'])->save($data);
        if(false !== $result){
        	return FIReturn("新增成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$express_id = input('post.express_id/d',0);
	    $result = $this->validate('Express.edit')->allowField(['express_name'])->save(['express_name'=>input('post.express_name')],['express_id'=>$express_id]);

        if(false !== $result){
        	return FIReturn("编辑成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d',0);
		$data = [];
		$data['status'] = -1;
	    $result = $this->update($data,['express_id'=>$id]);
        if(false !== $result){
        	return FIReturn("删除成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
}
