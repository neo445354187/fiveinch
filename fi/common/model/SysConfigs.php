<?php
namespace fi\common\model;
/**
 * 系统配置类
 */
class SysConfigs extends Base{
	
	/**
	 * 获取商城配置文件
	 */
	public function loadConfigs(){
		
		$rs = $this->field('field_code,field_value')->order("parent_id asc,fieldSort asc")->select();
		$configs = array();
		if(count($rs)>0){
			foreach ($rs as $key=>$v){
				if($v['field_code']=="hotSearchs"){
					$field_value = str_replace("，",",",$v['field_value']);
					$configs[$v['field_code']] = explode(",",$field_value);
				}else{
					$configs[$v['field_code']] = $v['field_value'];
				}
			}
		}
		unset($rs);
		return $configs;
	}
}
