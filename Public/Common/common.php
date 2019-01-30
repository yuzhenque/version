<?php
/**
 * 公用文件，用于初始化配置及加载函数库
 * 
 * @author bluefoot<bluefoot@qq.com>
 * @version 2014-06-02
 */

//自定义设置
global $g_cfg;
C($g_cfg);

require(ROOT . 'Public/Function/functions.php');
require(EXTEND_PATH . 'Function/extend.php');
?>
