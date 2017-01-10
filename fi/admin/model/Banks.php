<?php
namespace fi\admin\model;
/**
 * 银行业务处理
 */
class Banks extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->where('status',1)->field('bank_id,bank_name')->order('bank_id desc')->paginate(input('pagesize/d'));
	}
	public function getById($id){
		return $this->get(['bank_id'=>$id,'status'=>1]);
	}
	/**
	 * 列表
	 */
	public function listQuery(){
		return $this->where('status',1)->field('bank_id,bank_name')->select();
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = ['bank_name'=>input('post.bank_name'),
				 'create_time'=>date('Y-m-d H:i:s'),];
		$result = $this->validate('Banks.add')->allowField(['bank_name','create_time'])->save($data);
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
		$bank_id = input('post.bank_id/d',0);
	    $result = $this->validate('Banks.edit')->allowField(['bank_name'])->save(['bank_name'=>input('post.bank_name')],['bank_id'=>$bank_id]);

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
	    $result = $this->update($data,['bank_id'=>$id]);
        if(false !== $result){
        	return FIReturn("删除成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
}
