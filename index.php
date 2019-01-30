<?php
//数据类型，utf-8编码格式输出
@header('Content-Type: text/html; charset=utf-8');
@header('Cache-Control: no-store, no-cache, must-revalidate');

//垮域访问COOKIE
@header('P3P: CP="CAO PAS OUR"');

//程序根目录定义
define('ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
//程序入口 
define('CURSCRIPT', 'Admin');


//引入配置
require (ROOT . 'Conf/config.php');

//定义程序目录
define('APP_NAME', 'Admin');
define('APP_PATH', ROOT . 'Action/Admin/');
define('MODEL_PATH', ROOT . 'Lib/');
define('WIDGET_PATH', ROOT . 'Lib/');
define('TEMPLATE', 'web');

//定义ThinkPHP框架路径
define('THINK_PATH',ROOT . 'Public/ThinkPHP/');
//加载框架入口文件
require(THINK_PATH . '/ThinkPHP.php');

?>