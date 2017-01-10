<?php
namespace fi\admin\model;
/**
 * 角色志业务处理
 */
class Roles extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->where('status',1)->field('role_id,role_name')->paginate(input('pagesize/d'));
	}
	/**
	 * 列表
	 */
	public function listQuery(){
		return $this->where('status',1)->field('role_id,role_name')->select();
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d');
		$data = [];
		$data['status'] = -1;
	    $result = $this->update($data,['role_id'=>$id]);
        if(false !== $result){
        	return FIReturn("删除成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 获取角色权限
	 */
	public function getById($id){
		return $this->get(['status'=>1,'role_id'=>$id]);
	}
	
	/**
	 * 新增
	 */
	public function add(){
		$result = $this->validate('Roles.add')->allowField(true)->save(input('post.'));
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
		$id = input('post.role_id/d');
	    $result = $this->validate('Roles.edit')->allowField(true)->save(input('post.'),['role_id'=>$id]);
        if(false !== $result){
            $staff_role_id = (int)session('FI_STAFF.staff_role_id');
        	if($id==$staff_role_id){
        		$STAFF = session('FI_STAFF');
        		$STAFF['privileges'] = explode(',',input('post.privileges'));
        		$STAFF['role_name'] = Input('post.role_name');
        		session('FI_STAFF',$STAFF);
        	}
        	return FIReturn("编辑成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	
}
