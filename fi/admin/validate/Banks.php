<?php 
namespace fi\admin\validate;
use think\Validate;
/**
 * 银行验证器
 */
class Banks extends Validate{
	protected $rule = [
        ['bank_name'  ,'require|max:30','请输入银行名称|银行名称不能超过10个字符'],
    ];

    protected $scene = [
        'add'   =>  ['bank_name'],
        'edit'  =>  ['bank_name'],
    ]; 
}