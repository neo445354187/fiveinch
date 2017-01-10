<?php 
namespace fi\common\validate;
use think\Validate;
/**
 * 用户地址验证器
 */
class UserAddress extends Validate{
	protected $rule = [
        ['area_id'  ,'require','请选择地址'],
        ['user_address'  ,'require','请输入详细地址'],
        ['user_name'  ,'require','请输入联系名称'],
        ['is_default'  ,'in:0,1','请选择是否默认地址'],
        ['user_phone'  ,'require','请输入联系电话'],
    ];

    protected $scene = [
        'add'   =>  ['area_id','user_address','user_name','is_default','user_phone'],
        'edit'  =>  ['area_id','user_address','user_name','is_default','user_phone'],
    ]; 
}