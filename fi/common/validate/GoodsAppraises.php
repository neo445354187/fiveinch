<?php 
namespace fi\common\validate;
use think\Validate;
/**
 * 评价验证器
 */
class GoodsAppraises extends Validate{
	protected $rule = [
        ['goods_score'  ,'between:1,5','评分必须在1-5之间'],
        ['service_score'  ,'between:1,5','评分必须在1-5之间'],
        ['time_score'  ,'between:1,5','评分必须在1-5之间'],
        ['content'  ,'require|length:3,600','点评内容不能为空|点评内容应为3-200个字'],
    ];

    protected $scene = [
        'add'   =>  ['goods_score','service_score','time_score','content'],
    ]; 
}