<?php 
namespace fi\admin\validate;
use think\Validate;
/**
 * 权限验证器
 */
class Friendlinks extends Validate{
	protected $rule = [
	    ['friend_link_name'  ,'require|max:90','请输入网站名称|网站名称不能超过30个字符'],
        ['friend_link_url'  ,'require','请输入网址']
    ];

    protected $scene = [
        'add'   =>  ['friend_link_name'],
        'edit'  =>  ['friend_link_name'],
    ]; 
}