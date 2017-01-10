<?php
namespace fi\admin\model;
use think\Db;
/**
 * 广告位置业务处理
 */
class AdPositions extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->where('status',1)->field(true)->order('ap_sort asc,position_id asc')->paginate(input('pagesize/d'));
	}
	public function getById($id){
		return $this->get(['position_id'=>$id,'status'=>1]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		FIUnset($data,'position_id');
		$result = $this->validate('AdPositions.add')->allowField(true)->save($data);
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
		$Id = (int)input('post.position_id');
	    $result = $this->validate('AdPositions.edit')->allowField(true)->save(input('post.'),['position_id'=>$Id]);
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
	    $id = (int)input('post.id/d');
	    $result = $this->setField(['position_id'=>$id,'status'=>-1]);
        if(false !== $result){
        	return FIReturn("删除成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	/**
	* 获取广告位置
	*/
	public function getPositon($typeId){
		return $this->where(['position_type'=>$typeId,'status'=>1])->order('ap_sort asc,position_id asc')->select();
	}
	
}
