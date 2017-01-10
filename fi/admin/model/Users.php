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
		$where['status'] = 1;
		$lName = input('get.login_name1');
		$phone = input('get.loginPhone');
		$email = input('get.loginEmail');
		$uType = input('get.user_type');
		$uStatus = input('get.user_status1');
		if(!empty($lName))
			$where['login_name'] = ['like',"%$lName%"];
		if(!empty($phone))
			$where['user_phone'] = ['like',"%$phone%"];
		if(!empty($email))
			$where['user_email'] = ['like',"%$email%"];
		if(is_numeric($uType))
			$where['user_type'] = ['=',"$uType"];
		if(is_numeric($uStatus))
			$where['user_status'] = ['=',"$uStatus"];

		/********************* 取数据 *************************/
		$rs = $this->where($where)
					->field(['user_id','login_name','user_name','user_phone','user_email','user_score','create_time','user_status','last_time'])
					->order('user_id desc')
					->paginate(input('pagesize/d'));
		return $rs;
	}
	public function getById($id){
		return $this->get(['user_id'=>$id]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['create_time'] = date('Y-m-d H:i:s');
		$data["login_secret"] = rand(1000,9999);
    	$data['login_password'] = md5($data['login_password'].$data['login_secret']);
    	FIUnset($data,'user_id');
    	Db::startTrans();
		try{
			$result = $this->validate('Users.add')->allowField(true)->save($data);
			$id = $this->user_id;
	        if(false !== $result){
	        	FIUseImages(1, $id, $data['user_photo']);
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
		$Id = (int)input('post.user_id');
		$data = input('post.');
		//判断是否需要修改密码
		if(empty($data['login_password'])){
			unset($data['login_password']);
		}else{
			$data["login_secret"] = rand(1000,9999);
    		$data['login_password'] = md5($data['login_password'].$data['login_secret']);
		}
		Db::startTrans();
		try{
			if(isset($data['user_photo'])){
			    FIUseImages(1, $Id, $data['user_photo'], 'users', 'user_photo');
			}
			
			FIUnset($data,'create_time,user_id');
		    $result = $this->validate('Users.add')->allowField(true)->save($data,['user_id'=>$Id]);
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
			$data['status'] = -1;
		    $result = $this->update($data,['user_id'=>$id]);
	        if(false !== $result){
	        	FIUnuseImage('users','user_photo',$id);
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
		$result = $this->update(['user_status'=>(int)$status],['user_id'=>(int)$id]);
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
		return $this->field(['user_id','login_name'])->where(['login_name'=>['like',"%$name%"]])->select();
	}
	/**
	* 获取所有用户id
	*/
	public function getAllUserId()
	{
		return $this->where('status',1)->column('user_id');
	}
	
}
