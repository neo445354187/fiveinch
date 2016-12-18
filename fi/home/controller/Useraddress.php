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
        return model('userAddress')->setDefault();
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
        $userId = (int)session('FI_USER.userId');
        if(!$userId){
            return FIReturn('未登录', -1);
        }
        $list = model('Home/userAddress')->listQuery($userId);
        return FIReturn('', 1,$list);
    }
	
	/**
	* 跳去修改地址
	*/
	public function edit(){
		$m = model('userAddress');
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
        $m = model('userAddress');
        $rs = $m->add();
        return $rs;
    }
	/**
    * 修改
    */
    public function toEdit(){
        $m = model('userAddress');
        $rs = $m->edit();
        return $rs;
    }
    /**
    * 删除
    */
    public function del(){
    	$m = model('userAddress');
        $rs = $m->del();
        return $rs;
    }
    
    /**
     * 获取用户地址
     */
    public function getById(){
    	$m = model('userAddress');
        $id=(int)input('id');
        $data = $m->getById($id);
        return FIReturn('', 1,$data);
    }
}
