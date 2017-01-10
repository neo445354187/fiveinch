<?php 
namespace fi\admin\validate;
use think\Validate;
/**
 * 广告位置验证器
 */
class AdPositions extends Validate{
	protected $rule = [
	    ['position_name|max:30'  ,'require','请输入位置名称|位置名称不能超过10个字符'],
	    ['position_code|max:60'  ,'require','请输入位置代码|位置代码不能超过20个字符'],
		['position_type'  ,'require','请选择位置类型'],
	    ['position_width'  ,'require','请输入建议宽度'],
	    ['position_height'  ,'require','请输入建议高度'],
    ];

    protected $scene = [
        'add'   =>  ['position_name','position_code','position_type','position_width','position_height'],
        'edit'  =>  ['position_name','position_code','position_type','position_width','position_height'],
    ]; 
}