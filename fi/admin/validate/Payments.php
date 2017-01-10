<?php
namespace fi\admin\validate;
use think\Validate;
/**
 * 支付验证器
 */
class Payments extends Validate{
	protected $rule = [
		['pay_name','require','支付名称不能为空'],
		['pay_desc','require','支付描述不能为空'],
		['pay_order','require','排序号不能为空'],
	];
	protected $scene = [
		'edit'=>['pay_name','pay_desc','pay_order'],
	];
}