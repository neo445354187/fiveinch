<?php
namespace fi\admin\behavior;
/**
 * 初始化基础数据
 */
class InitConfig 
{
    public function run(&$params){
        FIConf('listenUrl',FIVisitPrivilege());
        FIConf('CONF',FIConfig());
    }
}