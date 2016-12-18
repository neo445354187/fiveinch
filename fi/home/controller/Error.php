<?php
namespace fi\home\controller;
/**
 * 错误处理控制器
 */
class Error extends Base{
    public function index(){
    	header("HTTP/1.0 404 Not Found");
        return $this->fetch('default/error_sys');
    }
}
