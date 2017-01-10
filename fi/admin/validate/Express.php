<?php 
namespace fi\admin\validate;
use think\Validate;
/**
 * 快递验证器
 */
class express extends Validate{
	protected $rule = [
        ['express_name'  ,'require|max:30','请输入快递名称|快递名称不能超过10个字符'],
    ];

    protected $scene = [
        'add'   =>  ['express_name'],
        'edit'  =>  ['express_name'],
    ]; 
}