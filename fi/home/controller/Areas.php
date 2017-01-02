<?php
namespace fi\home\controller;
use fi\common\model\Areas as M;


/**
 * 地区控制器
 */
class Areas extends Base{
	

	/**
	* 获取地区信息
	*/
    public function listQuery(){
        $m = new M();
        $rs = $m->listQuery();
        return FIReturn('', 1, $rs);
    }

    /**
     * [FunctionName 获取省市信息，并返回给前端]
     * @param string $value [description]
     */
   	public function getProvincesAndCities()
   	{
   		$res = model('Areas')->getProvincesAndCities();
   		if ($res) {
   			$this->result['data'] = $this->fetch('default/areas/getProvincesAndCities');
   		} else {
   			$this->result['status'] = CODE_FAIL;
   			$this->result['msg'] = lang('Failed to obtain regional information');
   		}
   		echo $this->result['data'];die;
   		$this->ajax($this->result);
   	}
}
