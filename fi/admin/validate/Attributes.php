<?php
namespace fi\admin\validate;
use think\Validate;
/**
 * 属性验证器
 */
class Attributes extends Validate{
	protected $rule = [
		['attr_name', 'require|max:60', '请输入属性名称|属性名称不能超过20个字符'],
		['attr_type','in:0,1,2','请选择属性类型'],
		['attr_val','checkattr_val:1','请输入发票说明'],
		['is_show','in:0,1','请选择是否显示']
	];
	protected $scene = [
		'add'=>['attr_name'],
		'edit'=>['attr_name'],
	];
	protected function checkattr_val(){
		if(input('post.attr_type/d')!=0 && input('post.attr_val')=='')return '请输入属性选项';
		return true;
	}
	
}