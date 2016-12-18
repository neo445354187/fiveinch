<?php
namespace fi\home\behavior;
/**
 * 初始化基础数据
 */
class InitConfig 
{
    public function run(&$params){
        FIConf('protectedUrl',model('HomeMenus')->getMenusUrl());
        FIConf('CONF',FIConfig());
    }
}