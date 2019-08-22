<?php

/**
 * ------------------------------
 * 本地及测试服务器的一些特殊配置
 *
 * @author gd. 2015-03-20
 * ------------------------------
 */

//缓存关键字前缀
define('G_MMC_PRE', 'rsisp');

//本域名程序主体配置
$g_domainCfg = array(
	'DOMAIN_TYPE'		=> 'local',
	'WEB_TITLE'			=> '发布管理平台',			// 网站名称

	/* URL设置 */
	'URL_CASE_INSENSITIVE'  => true,   				// 默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'             => 1,       				// URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式，提供最好的用户体验和SEO支持

	'APP_DEBUG'			=> true,						// 是否开启调试模式[true|false]

	'TOKEN_ON'			=> false,						//是否开启令牉验证
	'TOKEN_NAME'		=> '__hash__',					//令牉验证的表单隐藏字段名称
	'TOKEN_TYPE'		=> 'md5',						//令牉哈希验证觃则 默认为MD5
	'COOKIE_EXPIRE'		=> 21600000,					//Cookie有效期
	
	/* SESSION设置 */
    'VAR_SESSION_ID'    => 'PHPSESSID',					//sessionID的提交变量
	'SESSION_EXPIRE'	=> 86400,						//SESSION有效期

	'DEFAULT_THEME'		=> 'default',					//默认模板
	'TMPL_DETECT_THEME' => true,						//自动侦测模板主题
	'TMPL_CACHE_TIME'	=> -1,							//模板缓存有效期 -1 永久 单位为秒
	'TMPL_STRIP_SPACE'	=> false,						//是否去除模板文件里面的html空格与换行
	'TMPL_ACTION_ERROR' => 'Public:success',
	'TMPL_ACTION_SUCCESS' => 'Public:success',
	'SHOW_ERROR_MSG'	=> true,						//显示错误信息
	'VAR_AJAX_SUBMIT'	=> 'cprs',						//如果有此参数表明也是ajax方式提交过来的数据
	
	'VAR_PAGE'			=> 'p',							//分页变量
	'PAGE_NUM'			=> 10,							//每页显示条数
	'PAGE_ROLLPAGE'		=> 8,							//分页时显示几个链接
	'REPORT_SHOW'		=> 1,							//显示报表
);

//数据库配置
$g_dbCfg = array(
	'DB_TYPE'				=>	'Mysql',
	'DB_HOST'				=>	'47.98.150.174',
	'DB_USER'				=>	'svn_version',
	'DB_PWD'				=>	'ptCP3ePjbS',
	'DB_NAME'				=>	'svn_version',
	'DB_PREFIX'				=>	'',
	'DB_CHARSET'			=>	'utf8',
	'DB_FIELDTYPE_CHECK'	=>	false,
);

//缓存配置，这里可以加N组
$g_mmcCfg['MMC_CFG'] = array(
	array(
		'MMC_HOST' => '172.16.10.88',
		'MMC_PORT' => 11211,
		'MMC_TIMEOUT' => 3
	)
);
?>