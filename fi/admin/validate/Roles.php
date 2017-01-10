<?php 
namespace fi\admin\validate;
use think\Validate;
/**
 * 角色验证器
 */
class Roles extends Validate{
	protected $rule = [
        ['role_name'  ,'require|max:30','请输入角色名称|角色名称不能超过10个字符']
    ];

    protected $scene = [
        'add'   =>  ['menu_name'],
        'edit'  =>  ['menu_name']
    ]; 
}