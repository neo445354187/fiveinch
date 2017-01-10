<?php 
namespace fi\admin\validate;
use think\Validate;
use think\Db;
/**
 * 职员验证器
 */
class Staffs extends Validate{
	protected $rule = [
	    ['login_name'  ,'require|max:20|checkLoginName:1','请输入登录账号|登录账号不能超过20个字符'],
	    ['login_password'  ,'require|min:6','请输入登录密码|登录密码不能少于6个字符'],
        ['staff_name'  ,'require|max:60','请输入职员名称|职员名称不能超过20个字符'],
        ['work_itatus','require|in:0,1','请选择工作状态|无效的工作状态值'],
        ['staff_status','require|in:0,1','请选择账号状态|无效的账号状态值']
    ];

    protected $scene = [
        'add'   =>  ['login_name','login_password','staff_name','work_itatus','staff_status'],
        'edit'  =>  ['staff_name','work_itatus','staff_status']
    ]; 
    
    protected function checkLoginName($value){
    	$where = [];
    	$where['status'] = 1;
    	$where['login_name'] = $value;
    	$rs = Db::table('__STAFFS__')->where($where)->count();
    	return ($rs==0)?true:'该登录账号已存在';
    }
}