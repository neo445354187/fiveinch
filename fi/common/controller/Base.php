<?php
namespace fi\common\controller;
use think\Lang;
/**
 * 基础控制器
 */
class Base extends \think\Controller{
	/**
	 * [$result 返回消息，用法：在控制器中直接`return $this->result;`即可]
	 * @var [type]
	 */
	protected $result = [
		'status' => 1,
		'msg' => '',
		'data'=> [],
	];

	public function __construct()
    {
        parent::__construct();
    	//加载对应的与控制器名对应的语言包，自动加载里面有判断是否为file，所以不用再这里判断
   		Lang::load( APP_PATH . request()->module().'\\lang\\'.Lang::range().'\\'.request()->controller().'.php');
    }

    /**
     * [ajax json_encode功能]
     * @param  [type] $result [description]
     * @return [type]         [description]
     */
    public function ajax($result)
    {
    	return json_encode($result);
    }
}
