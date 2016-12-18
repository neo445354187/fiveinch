<?php 
namespace wstmart\admin\validate;
use think\Validate;
use think\Db;
/**
 * 会员验证器
 */
class Users extends Validate{
	protected $rule = [
        ['loginName'  ,'require|max:30|checkLoginName:1','请输入账号|账号不能超过10个字符'],
    ];

    protected $scene = [
        'add'   =>  ['loginName'],
        'edit'  =>  [],
    ]; 

    protected function checkLoginName($value){
    	$where = [];
    	$where['dataFlag'] = 1;
    	$where['loginName'] = $value;
    	$rs = Db::table('__USERS__')->where($where)->count();
    	return ($rs==0)?true:'该登录账号已存在';
    }
}