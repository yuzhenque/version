<?php

/**
 * ------------------------------
 * 域名管理逻辑
 * 
 * @author Bluefoot. 2012-06-27
 * ------------------------------
 */

//根据域名提取配置，无则启用默认配置
if(file_exists(ROOT . 'Conf/Logic/domain/' . $_SERVER['HTTP_HOST'] . '.cfg.php')){
	include_once(ROOT . 'Conf/Logic/domain/' . $_SERVER['HTTP_HOST'] . '.cfg.php');
}
//检查默认all.domain
elseif(file_exists(ROOT . 'Conf/Logic/domain/all.' . $_SERVER['HTTP_HOST'] . '.cfg.php')){
	include_once(ROOT . 'Conf/Logic/domain/all.' . $_SERVER['HTTP_HOST'] . '.cfg.php');
}
//检查有下一级域名的情况
elseif(file_exists(ROOT . 'Conf/Logic/domain/all' . substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.'), strlen($_SERVER['HTTP_HOST'])) . '.cfg.php')){
	include_once(ROOT . 'Conf/Logic/domain/all' . substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.'), strlen($_SERVER['HTTP_HOST'])) . '.cfg.php');
}else{
	include_once(ROOT . 'Conf/Logic/domain/local.cfg.php');
}

if($_SERVER['SERVER_PORT'] != '' && $_SERVER['SERVER_PORT'] != '80'){
	$domainUrl = 'http://' . $_SERVER['HTTP_HOST'] .':'.$_SERVER['SERVER_PORT']. '/';
}else{
	$domainUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/';
}

//提取正确的配置
if(isset($g_domainCfg)){
	
	//定义标题
	define('WEB_TITLE', !empty($g_domainCfg['WEB_TITLE']) ? $g_domainCfg['WEB_TITLE'] : 'RongNa');
	//URLMODE为1时加上脚本名
	if($g_domainCfg['URL_MODEL'] == 1 || $g_domainCfg['URL_MODEL'] == 3){
		$domainUrl .= str_replace('/', '', $_SERVER['SCRIPT_NAME']) .'/';
	}
	//定义主页地址
	define('WEB_INDEX', $domainUrl);
	//定义DEBUG模式
	define('APP_DEBUG', $g_domainCfg['APP_DEBUG']);
}
define('DOMAIN_URL', $domainUrl);
define('DOMAIN_TYPE', $g_domainCfg['DOMAIN_TYPE']);

define('IMG_URL', 'http://'. $_SERVER['HTTP_HOST']);
define('WEIXIN_DOMAIN', str_replace(array('www.', 'agent.'), '', $_SERVER['HTTP_HOST']));

if(APP_DEBUG == true){
	@error_reporting(E_ALL);
}

?>
