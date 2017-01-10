<?php 
namespace fi\common\validate;
use think\Validate;
/**
 * 门店分类验证器
 */
class ShopCats extends Validate{
	protected $rule = [
        ['cat_name'  ,'require|max:60','请输入分类名称|分类名称不能超过20个字符'],
        ['parent_id'  ,'number','无效的父级分类']
    ];

    protected $scene = [
        'add'   =>  ['cat_name','parent_id'],
        'edit'  =>  ['cat_name'],
    ]; 
}