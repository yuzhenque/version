<?php

/*
 * ----------------------------------------------+
 * 缓存配置文件
 * ----------------------------------------------+
 */


//定义日志路径
define('_DO_LOG_PATH', ROOT . 'sitedata/Logs/SqlLog/');
define('_DC_LOG_PATH', ROOT . 'sitedata/Logs/MmcLog/');

//************************************ DB相关设置 ************************
//定义日志记录类型
define('_DO_DEBUG', 0);
define('_DO_DEBUG_ECHO_SQL', 0);
define('_DO_DEBUG_ECHO_MYSQL_ERROR', 1);

//定义SQL日志记录哪些类型语句
define('_DO_LOG', 0);
define('_DO_LOG_SELECT', 1);
define('_DO_LOG_UPDATE', 1);
define('_DO_LOG_DELETE', 1);
define('_DO_LOG_INSERT', 1);
define('_DO_LOG_OTHER', 1);

//标识
define ( '_DO_MMC_VERSION_NAME', 'nnsd/*df8-sa' );
define ( '_DO_MMC_TABLE_NAME', 'nnsd/*af3f@!aa' );
define ( '_DO_MMC_UNKEY_CONF_NAME', 'nna2s%232+saa' );
define ( '_DO_MMC_UNKEY_NAME', 'nnsf&%$(=s45aa' );
define ( '_DO_MMC_NORMAL_NAME', 'nns*/f8/__-4aa' );

//************************************ MMC相关设置 ************************

//是否启用缓存，1 => disable memcache ; 0 => enable memcache
define('_DC_MMC_DISABLE', 1);
//是否使用 zlib 压缩 ,当flag=MEMCACHE_COMPRESSED的时侯，数据很小的时候不会采用zlib压缩，只有数据达到一定大小才对数据进行zlib压缩。
define('_DC_MMC_COMPRESS', 0);
//永不过期
define('_DC_MMC_EXPIRED_FOREVER', 0);
//默认过期时间，0为不过期
define('_DC_MMC_DEFAULT_EXPIRED', 0);
//key最大长度
define ( '_DC_MMC_KEY_MAX', 250 );

//开启MMC日志记录
define ( '_DC_LOG', 0 );
define ( '_DC_LOG_CRT_CACHE', 1 );
define ( '_DC_LOG_UPD_CACHE', 2 );
define ( '_DC_LOG_USE_CACHE', 3 );
define ( '_DC_LOG_DEL_CACHE', 4 );
define ( '_DC_LOG_LOW_DEL_CACHE', 5 );



//************************************ MMC表缓存配置 ************************
$g_cfg['MMC_TB_CFG'] = array ();

//后台表配置
$g_cfg['MMC_TB_CFG']['admin_user']		= array ('mmc' => true, 'log'=>false);
$g_cfg['MMC_TB_CFG']['admin_system']	= array ('mmc' => true, 'log'=>false);
$g_cfg['MMC_TB_CFG']['admin_role']		= array ('mmc' => true, 'log'=>false);
$g_cfg['MMC_TB_CFG']['admin_log']		= array ('mmc' => true, 'log'=>false);

//前台使用表配置
$g_cfg['MMC_TB_CFG']['user']		= array ('mmc' => true,	'log'=>false, 'unkey'=>array('id'));
$g_cfg['MMC_TB_CFG']['user_status'] = array ('mmc' => true,	'log'=>false, 'unkey'=>array('user_id'));

$g_cfg['MMC_TB_CFG']['system']				= array ('mmc' => true,	'log'=>false);

//用于缓存SHOW开头的SQL
$g_cfg['MMC_TB_CFG']['bluefoot_table_show']	= array ('mmc' => true,	'log'=>false);

//存储记录集
$g_cfg['MMC_RES'] = array (0 => 0 );
?>