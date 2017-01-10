<?php 
namespace fi\admin\validate;
use think\Validate;
/**
 * 权限验证器
 */
class GoodsAppraises extends Validate{
	protected $rule = [
		['is_show','require','状态不能为空'],
		['goods_score','number|gt:0','评分只能是数字|评分必须大于0'],
		['time_score','number|gt:0','评分只能是数字|评分必须大于0'],
		['service_score','number|gt:0','评分只能是数字|评分必须大于0'],
		['content','length:3,50','评价内容3-50个字'],
    ];

    protected $scene = [
        'edit'=>['is_show','goods_score','time_score','service_score','content'],
    ]; 
}