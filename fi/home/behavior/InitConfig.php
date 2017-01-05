<?php
namespace fi\home\behavior;
/**
 * 初始化基础数据
 */
class InitConfig 
{
    public function run(&$params){
    	//将保护url引入进来，getMenusUrl方法里面有cache，还算不错
        FIConf('protectedUrl',model('HomeMenus')->getMenusUrl());
        FIConf('CONF',FIConfig());
        //UID表示用户id，SID表示商家id
        //商家登陆会同时包含用户id和商家id，商家id并不是users表数据，而是shops表的shopId
        define('UID', (int) session('FI_USER.userId'));
        define('SID', (int) session('FI_USER.shopId'));
    }
}