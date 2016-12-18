<?php
namespace fi\home\controller;
use fi\home\model\Ads as M;
/**
 * 广告控制器
 */
class Ads extends Base{
	/**
	* 记录点击次数
	*/
    public function recordClick(){
        $m = new M();
        return $m->recordClick();
    }
}
