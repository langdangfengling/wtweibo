<?php
/**
 * 项目入口文件
 */
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
//define('BIND_MODULE','Admin');
define('APP_PATH','./App/');//定义项目路径，已经项目应用目录名
define('APP_DEBUG',true);//开启调试模式
require './ThinkPHP/ThinkPHP.php';