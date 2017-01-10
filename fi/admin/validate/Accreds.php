<?php 
namespace fi\admin\validate;
use think\Validate;
/**
 * 认证商家验证器
 */
class Accreds extends Validate{
	protected $rule = [
	    ['accred_name'  ,'require|max:30','请输入认证名称|认证名称不能超过30个字符'],
        ['accred_img'  ,'require','请上传图标']
    ];

    protected $scene = [
        'add'   =>  ['accred_name','accred_img'],
        'edit'  =>  ['accred_name'],
    ]; 
}