<?php 
namespace fi\admin\validate;
use think\Validate;
use think\Db;
/**
 * 会员验证器
 */
class Users extends Validate{
	protected $rule = [
        ['login_name'  ,'require|max:30|checkLoginName:1','请输入账号|账号不能超过10个字符'],
    ];

    protected $scene = [
        'add'   =>  ['login_name'],
        'edit'  =>  [],
    ]; 

    protected function checkLoginName($value){
    	$where = [];
    	$where['status'] = 1;
    	$where['login_name'] = $value;
    	$rs = Db::table('__USERS__')->where($where)->count();
    	return ($rs==0)?true:'该登录账号已存在';
    }
}