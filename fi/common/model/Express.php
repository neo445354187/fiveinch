<?php
namespace fi\common\model;
/**
 * 快递业务处理类
 */
use think\Db;
class Express extends Base{
    /**
	 * 获取快递列表
	 */
    public function listQuery(){
         return $this->where('status',1)->select();
    }
}
