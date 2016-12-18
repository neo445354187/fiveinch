<?php
namespace fi\common\exception;
/**
 */
use think\exception\Handle;

class FiHttpException extends Handle
{

    public function render(\Exception $e)
    {
    	if(config('app_debug')){
    		return parent::render($e);
    	}else{
    	    header("Location:".url('home/error/index'));
    	}
    }

}