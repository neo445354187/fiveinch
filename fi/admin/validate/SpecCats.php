<?php
namespace fi\admin\validate;
use think\Validate;
/**
 * 规格类型验证器
 */
class SpecCats extends Validate{
	protected $rule = [
		['cat_name|max:30', 'require', '请输入规格名称|规格名称不能超过10个字符'],
		['goods_cat_id','require|gt:0', '请选择所属商品分类'],
		['is_allow_img','require|in:0,1', '请选择是否显示允许上传图片'],
		['is_show','require|in:0,1', '请选择是否显示']
	];
	protected $scene = [
		'add'=>['cat_name','goods_cat_id','is_allow_img','is_show'],
		'edit'=>['cat_name','goods_cat_id','is_allow_img','is_show']
	];
	
}