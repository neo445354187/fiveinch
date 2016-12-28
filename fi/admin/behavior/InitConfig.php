<?php
namespace fi\admin\behavior;

/**
 * 初始化基础数据
 */
class InitConfig
{
    public function run(&$params)
    {
        FIConf('listenUrl', FIVisitPrivilege());
        //获取系统配置数据，数据表里的配置
        FIConf('CONF', FIConfig());
    }
}
