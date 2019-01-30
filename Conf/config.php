<?php
/**
 * ------------------------------
 * 主配置文件
 * 
 * @author Bluefoot. 2012-06-27
 * ------------------------------
 */

//定义程序运行时的文件缓存路径
define('RUNTIME_PATH', ROOT . 'SiteData/Runtime/'. CURSCRIPT .'/');
//定义程序日志路径
define('LOG_PATH',  ROOT . 'SiteData/Logs/'. CURSCRIPT .'/');
//定义公用文件目录
define('COMMON_PATH', ROOT . 'Public/Common/');
//公共类库库存放目录
define('PUBLIC_CLASS_PATH', ROOT . 'Public/Class/');

//提醒标志
define('CODE_SUCCESS', 1);//操作成功状态标识码
define('CODE_ERROR'  , 0);//错标识码
define('CODE_WARN'   , 2);//警告信息标识码	
define('CODE_INFO'   , 3);//普通信息提示标识码

//时间格式设置
define('DT_YM', 'Y-m');
define('DT_YMD', 'Y-m-d');
define('DT_YMDH', 'Y-m-d H');
define('DT_YMDHI', 'Y-m-d H:i');
define('DT_YMDHIS', 'Y-m-d H:i:s');
define('DT_HIS', 'H:i:s');
define('DT_HI', 'H:i');
define('DT_YYMMDD', 'Y年m月d日');
//时间变量
define('TIMESTAMP', time());

//程序配置
global $g_cfg, $g_domainCfg, $g_dbCfg, $g_mmcCfg;
global $g_activeCfg, $g_chatCfg, $g_fmlCfg, $g_increaseCfg;
global $g_debugCfg, $g_menuCfg, $g_settingCfg, $g_topNavCfg;

$g_cfg = array();
$g_activeCfg = $g_chatCfg = $g_fmlCfg = $g_increaseCfg = array();
$g_debugCfg = $g_menuCfg = $g_settingCfg = $g_topNavCfg = array();

//去除域名端口
$server = explode(":", $_SERVER['HTTP_HOST']);
$_SERVER['HTTP_HOST'] = $server[0];

//设置加密的KEY
define('AUTHKEY', '@#*&^HFROPKFEddddDRBM&^');

//百度MAPKEY
$g_cfg['BAIDU_MAP_AK'] = 'QiEuWN848QFs2a6hqyoh8ol3';

//引入域名、语言等等配置
require_once( ROOT.'Conf/Logic/mmc.cfg.php' );
require_once( ROOT.'Conf/Logic/domain.cfg.php' );
require_once( ROOT.'Conf/logic.cfg.php' );


//官方后台
if(CURSCRIPT == 'Admin'){
	require_once( ROOT.'Conf/Admin/menu.cfg.php' );
	require_once( ROOT.'Conf/Admin/setting.cfg.php' );
	$g_domainCfg['URL_MODEL'] = 1;
	$g_domainCfg['SESSION_PREFIX'] = 'admin_';
}

//关键字回复最多几条
$g_cfg['REPLY_KEYWORD_SHOW_MAX'] = 5;

//一级菜单个数
$g_cfg['LEVEL_FIRST_MAX'] = 3;

//二级菜单个数
$g_cfg['LEVEL_SECOND_MAX'] = 5;

//定义程序版本，用于缓存
$g_cfg['VERSION'] = '1.0.1';

//所有配置组合起来，调用时使用C('KEY')
$g_cfg = array_merge($g_cfg, $g_domainCfg, $g_dbCfg, $g_mmcCfg, $g_menuCfg, $g_logicCfg);

//注销变量
unset($g_domainCfg, $g_dbCfg, $g_mmcCfg,
	  $g_debugCfg, $g_menuCfg, $g_settingCfg, $g_topNavCfg);
