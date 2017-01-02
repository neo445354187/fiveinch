<?php

/**
 * 定义特征码常量，防止代码中直接出现数字特征码
 *
 */
//成功、正常、失败的状态码
define('CODE_SUCCESS', 1);
define('CODE_NORMAL', 0);
define('CODE_FAIL', -1);

//针对于数据库的dataFlag字段状态
define('FLAG_ENABLE', 1);
define('FLAG_HIDE', 0);
define('FLAG_DELETE', -1);
