<?php
namespace fi\admin\model;
use think\Db;
/**
 * 会员业务处理
 */
class Users extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		/******************** 查询 ************************/
		$where = [];
		$where['dataFlag'] = 1;
		$lName = input('get.loginName1');
		$phone = input('get.loginPhone');
		$email = input('get.loginEmail');
		$uType = input('get.userType');
		$uStatus = input('get.userStatus1');
		if(!empty($lName))
			$where['loginName'] = ['like',"%$lName%"];
		if(!empty($phone))
			$where['userPhone'] = ['like',"%$phone%"];
		if(!empty($email))
			$where['userEmail'] = ['like',"%$email%"];
		if(is_numeric($uType))
			$where['userType'] = ['=',"$uType"];
		if(is_numeric($uStatus))
			$where['userStatus'] = ['=',"$uStatus"];

		/********************* 取数据 *************************/
		$rs = $this->where($where)
					->field(['userId','loginName','userName','userPhone','userEmail','userScore','createTime','userStatus','lastTime'])
					->order('userId desc')
					->paginate(input('pagesize/d'));
		return $rs;
	}
	public function getById($id){
		return $this->get(['userId'=>$id]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createTime'] = date('Y-m-d H:i:s');
		$data["loginSecret"] = rand(1000,9999);
    	$data['loginPwd'] = md5($data['loginPwd'].$data['loginSecret']);
    	FIUnset($data,'userId');
    	Db::startTrans();
		try{
			$result = $this->validate('Users.add')->allowField(true)->save($data);
			$id = $this->userId;
	        if(false !== $result){
	        	FIUseImages(1, $id, $data['userPhoto']);
	        	Db::commit();
	        	return FIReturn("新增成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return FIReturn('新增失败',-1);
        }	
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$Id = (int)input('post.userId');
		$data = input('post.');
		//判断是否需要修改密码
		if(empty($data['loginPwd'])){
			unset($data['loginPwd']);
		}else{
			$data["loginSecret"] = rand(1000,9999);
    		$data['loginPwd'] = md5($data['loginPwd'].$data['loginSecret']);
		}
		Db::startTrans();
		try{
			if(isset($data['userPhoto'])){
			    FIUseImages(1, $Id, $data['userPhoto'], 'users', 'userPhoto');
			}
			
			FIUnset($data,'createTime,userId');
		    $result = $this->validate('Users.add')->allowField(true)->save($data,['userId'=>$Id]);
	        if(false !== $result){
	        	Db::commit();
	        	return FIReturn("编辑成功", 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return FIReturn('编辑失败',-1);
        }
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = (int)input('post.id');
	    Db::startTrans();
	    try{
		    $data = [];
			$data['dataFlag'] = -1;
		    $result = $this->update($data,['userId'=>$id]);
	        if(false !== $result){
	        	FIUnuseImage('users','userPhoto',$id);
	        	Db::commit();
	        	return FIReturn("删除成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
            return FIReturn('编辑失败',-1);
        }
	}
	/**
	* 是否启用
	*/
	public function changeUserStatus($id, $status){
		$result = $this->update(['userStatus'=>(int)$status],['userId'=>(int)$id]);
		if(false !== $result){
        	return FIReturn("删除成功", 1);
        }else{
        	return FIReturn($this->getError(),-1);
        }
	}
	/**
	* 根据用户名查找用户
	*/
	public function getByName($name){
		return $this->field(['userId','loginName'])->where(['loginName'=>['like',"%$name%"]])->select();
	}
	/**
	* 获取所有用户id
	*/
	public function getAllUserId()
	{
		return $this->where('dataFlag',1)->column('userId');
	}
	
}
