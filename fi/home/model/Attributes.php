<?php
namespace fi\home\model;
/**
 * 商品属性分类
 */
class Attributes extends Base{
	/**
	 * 获取可供筛选的商品属性
	 */
	public function listQueryByFilter($cat_id){
		$ids = model('GoodsCats')->getParentIs($cat_id);
		if(!empty($ids)){
			$cat_ids = [];
			foreach ($ids as $key =>$v){
				$cat_ids[] = $v;
			}
			$attrs = $this->where(['goods_cat_id'=>['in',$cat_ids],'is_show'=>1,'status'=>1,'attr_type'=>['<>',0]])
			     ->field('attr_id,attr_name,attr_val')->order('attr_sort asc')->select();
			foreach ($attrs as $key =>$v){
			    $attrs[$key]['attr_val'] = explode(',',$v['attr_val']);
			}
			return $attrs;
		}
		return [];
	}
}
