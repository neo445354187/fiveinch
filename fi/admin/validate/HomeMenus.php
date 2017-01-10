<?php 
namespace fi\admin\validate;
use think\Validate;
/**
 * 菜单验证器
 */
class HomeMenus extends Validate{
	protected $rule = [
        ['menu_name'  ,'require|max:30','请输入菜单名称|菜单名称不能超过10个字符'],
		['parent_id'  ,'number','无效的父级菜单'],
		['menu_type'  ,'require','请输入菜单类型'],
		['menu_url'   ,'require','请输入菜单Url'],
		['is_show'    ,'require','请选择是否显示']
    ];

    protected $scene = [
        'add'   =>  ['menu_name','parent_id','menu_type','menu_url','is_show'],
        'edit'  =>  ['menu_name','menu_type','menu_url','is_show']
    ]; 
}