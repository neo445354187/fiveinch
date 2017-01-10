<?php 
namespace fi\common\validate;
use think\Validate;
/**
 * 订单投诉验证器
 */
class OrderComplains extends Validate{
	protected $rule = [
        ['complain_type'  ,'in:1,2,3,4','无效的投诉类型！'],
        ['complain_content'  ,'require|length:3,600','投诉内容不能为空|投诉内容应为3-200个字'],
        ['respond_content'  ,'require|length:3,600','应诉内容不能为空|应诉内容应为3-200个字'],
    ];

    protected $scene = [
        'add'   =>  ['complain_type','complain_content'],
        'edit'   =>  ['complain_type','complain_content'],
        'respond' =>['respond_content'],
    ]; 
}