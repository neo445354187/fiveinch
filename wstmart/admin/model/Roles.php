<?php
namespace wstmart\admin\model;
/**
 * 角色志业务处理
 */
class Roles extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->where('dataFlag',1)->field('roleId,roleName')->paginate(input('pagesize/d'));
	}
	/**
	 * 列表
	 */
	public function listQuery(){
		return $this->where('dataFlag',1)->field('roleId,roleName')->select();
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d');
		$data = [];
		$data['dataFlag'] = -1;
	    $result = $this->update($data,['roleId'=>$id]);
        if(false !== $result){
        	return WSTReturn("删除成功", 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 获取角色权限
	 */
	public function getById($id){
		return $this->get(['dataFlag'=>1,'roleId'=>$id]);
	}
	
	/**
	 * 新增
	 */
	public function add(){
		$result = $this->validate('Roles.add')->allowField(true)->save(input('post.'));
        if(false !== $result){
        	return WSTReturn("新增成功", 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$id = input('post.roleId/d');
	    $result = $this->validate('Roles.edit')->allowField(true)->save(input('post.'),['roleId'=>$id]);
        if(false !== $result){
            $staffRoleId = (int)session('WST_STAFF.staffRoleId');
        	if($id==$staffRoleId){
        		$STAFF = session('WST_STAFF');
        		$STAFF['privileges'] = explode(',',input('post.privileges'));
        		$STAFF['roleName'] = Input('post.roleName');
        		session('WST_STAFF',$STAFF);
        	}
        	return WSTReturn("编辑成功", 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
	
}
