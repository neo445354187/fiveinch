<?php
namespace fi\home\controller;
/**
 * 积分控制器
 */
class Userscores extends Base{
    /**
    * 查看商城消息
    */
	public function index(){
		$rs = model('Users')->getFieldsById((int)session('FI_USER.user_id'),['user_score','user_total_score']);
		$this->assign('object',$rs);
		return $this->fetch('default/users/userscores/list');
	}
    /**
    * 获取数据
    */
    public function pageQuery(){
        $user_id = (int)session('FI_USER.user_id');
        $data = model('UserScores')->pageQuery($user_id);
        return FIReturn("", 1,$data);
    }
}
