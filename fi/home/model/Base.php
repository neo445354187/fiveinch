<?php
namespace fi\home\model;
use think\Db;
/**
 * 基础模型器
 */

class Base extends  \fi\common\model\Base 
{
	/**
	 * 获取空模型
	 */
	// public function getEModel($tables){
	// 	$rs =  Db::query('show columns FROM `'.config('database.prefix').$tables."`");
	// 	$obj = [];
	// 	if($rs){
	// 		foreach($rs as $key => $v) {
	// 			$obj[$v['Field']] = $v['Default'];
	// 			if($v['Key'] == 'PRI')$obj[$v['Field']] = 0;
	// 		}
	// 	}
	// 	return $obj;
	// }
}