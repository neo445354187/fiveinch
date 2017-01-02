<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 检测PHP环境
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    die('require PHP > 5.4.0 !');
}

// [ 应用入口文件 ]
$dir = realpath(__DIR__ . '/..');
// 定义应用目录
define('APP_PATH', $dir . '/fi/');
define('CONF_PATH', $dir . '/fi/common/conf/');
define('FI_COMM', $dir . '/fi/common/common/');
define('FI_HOME_COMM', $dir . '/fi/home/common/');
define('FI_ADMIN_COMM', $dir . '/fi/admin/common/');
// 加载框架引导文件
require $dir . '/thinkphp/start.php';
