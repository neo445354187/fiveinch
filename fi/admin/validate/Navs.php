<?php
namespace fi\admin\validate;
use think\Validate;
/**
 * 导航验证器
 */
class Navs extends Validate{
	protected $rule = [
		['nav_title|max:30', 'require', '请输入导航名称|导航名称不能超过10个字符'],
		['nav_url','require', '请输入导航链接'],
	];
	protected $scene = [
		'add'=>['nav_title','nav_url'],
		'edit'=>['nav_title','nav_url'],
	];
	
}