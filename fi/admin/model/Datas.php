<?php
namespace fi\admin\model;
/**
 * 经营范围业务处理
 */
use think\Db;
class Datas extends Base{
	/**
	 * 获取指定分类的列表
	 */
	public function listQuery($cat_id){
		return Db::table('__DATAS__')->where('cat_id',$cat_id)->field('data_name,data_val')->select();
	}
}
