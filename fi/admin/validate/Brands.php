<?php 
namespace fi\admin\validate;
use think\Validate;
/**
 * 权限验证器
 */
class Brands extends Validate{
	protected $rule = [
	    ['brand_name'  ,'require|max:60','请输入品牌名称|品牌名称不能超过20个字符'],
		['brand_img'  ,'require','请上传品牌图标'],
		['brand_desc'  ,'require','请输入品牌介绍']
    ];

    protected $scene = [
        'add'   =>  ['brand_name','brand_img','brand_desc'],
        'edit'  =>  ['brand_name','brand_img','brand_desc']
    ]; 
}