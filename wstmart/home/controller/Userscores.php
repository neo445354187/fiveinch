<?php
namespace wstmart\home\controller;
/**
 * 积分控制器
 */
class Userscores extends Base{
    /**
    * 查看商城消息
    */
	public function index(){
		$rs = model('Users')->getFieldsById((int)session('WST_USER.userId'),['userScore','userTotalScore']);
		$this->assign('object',$rs);
		return $this->fetch('default/users/userscores/list');
	}
    /**
    * 获取数据
    */
    public function pageQuery(){
        $userId = (int)session('WST_USER.userId');
        $data = model('UserScores')->pageQuery($userId);
        return WSTReturn("", 1,$data);
    }
}
