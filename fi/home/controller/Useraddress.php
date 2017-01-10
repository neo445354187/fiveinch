<?php
namespace fi\home\controller;
use fi\common\model\LogSms;
/**
 * 用户地址控制器
 */

class Useraddress extends Base{
    /**
    * 设置为默认地址
    */
    public function setDefault(){
        return model('user_address')->setDefault();
    }
	public function index(){
		return $this->fetch('default/users/useraddress/list');
	}
    /**
    * 获取地址信息
    * 1.购物车结算有引用
    */
    public function listQuery(){
        //获取用户信息
        $user_id = (int)session('FI_USER.user_id');
        if(!$user_id){
            return FIReturn('未登录', -1);
        }
        $list = model('Home/user_address')->listQuery($user_id);
        return FIReturn('', 1,$list);
    }
	
	/**
	* 跳去修改地址
	*/
	public function edit(){
		$m = model('user_address');
		$id=(int)input('id');
        $data = $m->getById($id);
        //获取省级地区信息
        $area1 = model('Areas')->listQuery(0);
        $this->assign(['data'=>$data,
        			   'area1'=>$area1]);
		return $this->fetch('default/users/useraddress/edit');
	}
	/**
     * 新增
     */
    public function add(){
        $m = model('user_address');
        $rs = $m->add();
        return $rs;
    }
	/**
    * 修改
    */
    public function toEdit(){
        $m = model('user_address');
        $rs = $m->edit();
        return $rs;
    }
    /**
    * 删除
    */
    public function del(){
    	$m = model('user_address');
        $rs = $m->del();
        return $rs;
    }
    
    /**
     * 获取用户地址
     */
    public function getById(){
    	$m = model('user_address');
        $id=(int)input('id');
        $data = $m->getById($id);
        return FIReturn('', 1,$data);
    }
}
