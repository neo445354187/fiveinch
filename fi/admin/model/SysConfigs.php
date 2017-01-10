<?php
namespace fi\admin\model;
/**
 * 商城配置业务处理
 */
use think\Db;
class SysConfigs extends Base{
	/**
	 * 获取商城配置
	 */
	public function getSysConfigs(){
		$rs = $this->field('field_code,field_value')->select();
		$rv = [];
		foreach ($rs as $v){
			$rv[$v['field_code']] = $v['field_value'];
		}
		return $rv;
	}

	
    /**
	 * 编辑
	 */
	public function edit(){
		$list = $this->field('config_id,field_code,field_value')->select();
		Db::startTrans();
        try{
			foreach ($list as $key =>$v){
				$code = trim($v['field_code']);
				$val = Input('post.'.trim($v['field_code']));
			    //启用图片
				if(substr($val,0,7)=='upload/' && strpos($val,'.')!==false){
					FIUseImages(1, $v['config_id'],$val, 'sys_configs','field_value');
				}
				$this->update(['field_value'=>$val],['field_code'=>$code]);
			}
			Db::commit(); 
			cache('FI_CONF',null);
			return FIReturn("操作成功", 1);
        }catch (\Exception $e) {
        	print_r($e);
		    Db::rollback();
		}
		return FIReturn("操作失败", 1);
	}
	
}
