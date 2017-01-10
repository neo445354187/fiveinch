<?php 
namespace fi\admin\validate;
use think\Validate;
/**
 * 会员级别验证器
 */
class UserRanks extends Validate{
	protected $rule = [
        ['rank_name'  ,'require|max:30','请输入会员等级名称|会员等级名称不能超过10个字符'],
    ];

    protected $scene = [
        'add'   =>  ['rank_name'],
        'edit'  =>  ['rank_name'],
    ]; 

    
}