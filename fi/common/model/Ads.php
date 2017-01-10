<?php
namespace fi\common\model;
/**
 * 广告类
 */
class Ads extends Base{
	public function recordClick(){
		$id = (int)input('id');
		return $this->where(['ad_id'=>$id])->setInc('ad_click_num');
	}
}
