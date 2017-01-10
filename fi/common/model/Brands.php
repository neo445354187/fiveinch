<?php
namespace fi\common\model;
use think\Db;
/**
 * 品牌业务处理类
 */
class Brands extends Base{
	/**
	 * 获取品牌列表
	 */
	public function pageQuery($pagesize){
		$id = (int)input('id');
		$where['b.status']=1;
		if($id>1){
			$where['gcb.cat_id']=$id;
		}
		$rs = $this->alias('b')
				   ->join('__CAT_BRANDS__ gcb','gcb.brand_id=b.brand_id','left')
				   ->where($where)
				   ->field('b.brand_id,brand_name,brand_img,gcb.cat_id')
				   ->paginate($pagesize)->toArray();
		return $rs;
	}
	/**
	 * 获取品牌列表
	 */
	public function listQuery($cat_id){
		$rs = Db::table('__CAT_BRANDS__')->alias('l')->join('__BRANDS__ b','b.brand_id=l.brand_id and b.status=1 and l.cat_id='.$cat_id)
		          ->field('b.brand_id,b.brand_name')->select();
		return $rs;
	}
}
