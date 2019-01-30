-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        5.6.17-log - MySQL Community Server (GPL)
-- 服务器操作系统:                      Win64
-- HeidiSQL 版本:                  9.4.0.5174
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- 导出 svn_version 的数据库结构
CREATE DATABASE IF NOT EXISTS `svn_version` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `svn_version`;

-- 导出  表 svn_version.admin_log 结构
CREATE TABLE IF NOT EXISTS `admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `http_type` enum('post','get','ajax') COLLATE utf8_unicode_ci DEFAULT 'get' COMMENT '请求方式',
  `module_name` varchar(30) CHARACTER SET utf8 DEFAULT NULL COMMENT '操作所在的控制器名',
  `action_name` varchar(500) CHARACTER SET utf8 DEFAULT NULL COMMENT '进行操作名称',
  `action_user` varchar(20) CHARACTER SET utf8 DEFAULT NULL COMMENT '操作者',
  `action_url` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '对应操作的资源链接地址',
  `action_time` datetime DEFAULT NULL COMMENT '操作时间',
  `action_ip` varchar(15) CHARACTER SET utf8 DEFAULT NULL COMMENT '操作所在IP地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- 正在导出表  svn_version.admin_log 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `admin_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_log` ENABLE KEYS */;

-- 导出  表 svn_version.admin_role 结构
CREATE TABLE IF NOT EXISTS `admin_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resources` text COLLATE utf8_unicode_ci,
  `channels` text COLLATE utf8_unicode_ci,
  `status` int(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- 正在导出表  svn_version.admin_role 的数据：~2 rows (大约)
/*!40000 ALTER TABLE `admin_role` DISABLE KEYS */;
INSERT INTO `admin_role` (`id`, `title`, `resources`, `channels`, `status`) VALUES
	(1, '超级管理员', NULL, NULL, 100),
	(2, '管理员', '["ErpVersion-index","ErpVersion-add","ErpVersion-edit","ErpVersion-delete","WwwVersion-index","WwwVersion-add","WwwVersion-edit","WwwVersion-delete","CloudVersion-index","CloudVersion-add","CloudVersion-edit","CloudVersion-delete","AdminUser-editpass","Sync-index"]', NULL, 1);
/*!40000 ALTER TABLE `admin_role` ENABLE KEYS */;

-- 导出  表 svn_version.admin_user 结构
CREATE TABLE IF NOT EXISTS `admin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL COMMENT '角色ID',
  `user_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户类型：1超级管理员，0普通管理员 -1禁止访问',
  `username` char(20) CHARACTER SET utf8 DEFAULT NULL COMMENT '登录用户名',
  `password` char(32) CHARACTER SET utf8 DEFAULT NULL COMMENT '加密后的密码',
  `sex` enum('女','男') COLLATE utf8_unicode_ci DEFAULT '男',
  `surename` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `phone` char(11) CHARACTER SET utf8 DEFAULT NULL COMMENT '手机号码',
  `email` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '邮箱地址',
  `addtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `login_time` datetime DEFAULT NULL COMMENT '最后一次登录时间',
  `login_ip` char(15) CHARACTER SET utf8 DEFAULT '000.000.000.000' COMMENT '最后一次登录IP',
  `login_error` int(1) DEFAULT '0' COMMENT '登录错误次数',
  `status` tinyint(1) DEFAULT '1' COMMENT '被锁定状态，1锁定（锁定后不能进行登录）',
  `permissions` text CHARACTER SET utf8 COMMENT '可操作权限列表',
  `warehouse_id` int(11) NOT NULL DEFAULT '1' COMMENT '所属仓库ID',
  `is_yewu` tinyint(1) NOT NULL DEFAULT '2' COMMENT '1业务负责2否',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- 正在导出表  svn_version.admin_user 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `admin_user` DISABLE KEYS */;
INSERT INTO `admin_user` (`id`, `role_id`, `user_type`, `username`, `password`, `sex`, `surename`, `phone`, `email`, `addtime`, `login_time`, `login_ip`, `login_error`, `status`, `permissions`, `warehouse_id`, `is_yewu`) VALUES
	(1, 1, 1, 'admin', '7523370ff85a9d437b7cc1fb6198fea4', '男', '管理员', '18205926991', '', 1369032818, '2014-05-31 10:56:58', '127.0.0.1', 0, 1, '', 1, 2);
/*!40000 ALTER TABLE `admin_user` ENABLE KEYS */;

-- 导出  表 svn_version.file 结构
CREATE TABLE IF NOT EXISTS `file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boss_id` int(11) NOT NULL DEFAULT '0' COMMENT '谁上传的',
  `name` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '原文件名',
  `file_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '重命名后的文件名',
  `file_size` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '文件大小',
  `file_ext` varchar(20) CHARACTER SET utf8 DEFAULT NULL COMMENT '文件格式',
  `save_path` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '存储路径',
  `file_path` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '文件所在的文件夹',
  `root_path` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `upload_time` int(10) DEFAULT NULL COMMENT '上传时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- 正在导出表  svn_version.file 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `file` DISABLE KEYS */;
/*!40000 ALTER TABLE `file` ENABLE KEYS */;

-- 导出  表 svn_version.server 结构
CREATE TABLE IF NOT EXISTS `server` (
  `server_id` int(11) NOT NULL AUTO_INCREMENT,
  `server_type` tinyint(1) NOT NULL DEFAULT '1',
  `server_title` varchar(50) NOT NULL DEFAULT '',
  `server_ip` varchar(110) NOT NULL DEFAULT '',
  `server_port` int(8) NOT NULL COMMENT '端口',
  `server_domain` varchar(110) NOT NULL DEFAULT '',
  `server_account` varchar(110) NOT NULL DEFAULT '',
  `server_password` varchar(110) NOT NULL DEFAULT '',
  `server_local_path` varchar(110) NOT NULL DEFAULT '',
  `server_sync_path` varchar(110) NOT NULL DEFAULT '',
  `server_sync_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1sftp2ftp',
  PRIMARY KEY (`server_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- 正在导出表  svn_version.server 的数据：~2 rows (大约)
/*!40000 ALTER TABLE `server` DISABLE KEYS */;
INSERT INTO `server` (`server_id`, `server_type`, `server_title`, `server_ip`, `server_port`, `server_domain`, `server_account`, `server_password`, `server_local_path`, `server_sync_path`, `server_sync_type`) VALUES
	(1, 5, '舜天', '218.2.111.165', 21, 'http://erp.shuntianu.com', 'erp.shuntianu.com', 'FBDECx64Cw', 'D:/wamp/www/ERP_ST/', '/', 2),
	(3, 6, '舜天-官网', '118.178.120.106', 22, 'http://w.shuntianu.com', 'root', 'Charmy198806021236', '/Users/wuxiangrong/My/Htdocs/ShuntianWWW/', '/data/www/erp_zhuanxian_www/', 1);
/*!40000 ALTER TABLE `server` ENABLE KEYS */;

-- 导出  表 svn_version.system 结构
CREATE TABLE IF NOT EXISTS `system` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '所属用户',
  `type` varchar(20) CHARACTER SET utf8 DEFAULT NULL COMMENT '类型',
  `identy` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `attvalue` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- 正在导出表  svn_version.system 的数据：~29 rows (大约)
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
INSERT INTO `system` (`id`, `user_id`, `type`, `identy`, `attvalue`) VALUES
	(1, 1, 'base', 'SAFE_DAYS', '5'),
	(2, 0, 'base', 'WEB_TITLE', 'ERP-版本发布管理系统'),
	(3, 0, 'base', 'WEB_COPYRIGHT', 'COPY'),
	(4, 0, 'base', 'WEB_PHONE', '123123'),
	(5, 0, 'base', 'WEB_WEIXIN', '123123'),
	(6, 0, 'base', 'WEB_QQ', '123123'),
	(7, 0, 'system', 'SYSTEM_PER', '7'),
	(8, 0, 'weixin', 'WX_TYPE', '2'),
	(9, 0, 'weixin', 'WX_CERT', '1'),
	(10, 0, 'weixin', 'WX_TOKEN', 'mytoken'),
	(11, 0, 'weixin', 'WX_APPID', 'wxe977af3b6284b1ae'),
	(12, 0, 'weixin', 'WX_APPSECRET', '50c40315bf0526e383efc22fd1110652'),
	(13, 0, 'wxpay', 'WXPAY_APPID', 'wxe977af3b6284b1ae'),
	(14, 0, 'wxpay', 'WXPAY_APPSECRET', '50c40315bf0526e383efc22fd1110652'),
	(15, 0, 'wxpay', 'WXPAY_PAYKEY', '50c40315bf05abedoeqfc22fd1110652'),
	(16, 0, 'wxpay', 'WXPAY_MCHID', '1244357202'),
	(17, 0, 'sms', 'SMS_TYPE', 'smsbao'),
	(18, 0, 'sms', 'SMS_DB_HOST', 'http://www.smsbao.com/'),
	(19, 0, 'sms', 'SMS_DB_USER', 'xmlvxianfeng'),
	(20, 0, 'sms', 'SMS_DB_PASS', 'xmlvxianfeng123'),
	(21, 0, 'sms', 'SMS_TEXT', '【绿鲜丰】'),
	(22, 0, 'system', 'USER_LEVEL_1', '2'),
	(23, 0, 'system', 'USER_FY_1_1', '0.0043'),
	(24, 0, 'system', 'USER_FY_1_2', '0'),
	(25, 0, 'system', 'USER_LEVEL_2', '4'),
	(26, 0, 'system', 'USER_FY_2_1', '0.0039'),
	(27, 0, 'system', 'USER_FY_2_2', '0'),
	(28, 0, 'weixin', 'WX_SUB_CONTENT', '感谢您的关注\r\n<a href="http://www.xmlvxianfeng.com/wap.php">进入首页</a>'),
	(29, 0, 'weixin', 'WX_OTHER_CONTENT', '/:,@-D您好，请问有什么可以帮到您！\r\n<a href="http://www.xmlvxianfeng.com/wap.php">进入首页</a>');
/*!40000 ALTER TABLE `system` ENABLE KEYS */;

-- 导出  表 svn_version.version 结构
CREATE TABLE IF NOT EXISTS `version` (
  `version_id` int(11) NOT NULL AUTO_INCREMENT,
  `version_type_id` int(11) NOT NULL DEFAULT '0' COMMENT '类型ID',
  `version_name` varchar(30) DEFAULT NULL COMMENT '版本名称',
  `version_plan_time` int(10) DEFAULT NULL COMMENT '预计发布时间',
  `version_release_time` int(10) DEFAULT NULL COMMENT '正式上线时间',
  `version_content` longtext COMMENT '发布内容',
  `version_database` longtext,
  `version_database_view` longtext COMMENT '视图更新',
  `version_basic` longtext,
  `version_file` longtext,
  `version_file_not_exsits` text,
  `version_file_filter` text COMMENT '最后一次过滤的文件',
  `version_script` text,
  `version_auto_script` text,
  `is_delete` tinyint(1) NOT NULL DEFAULT '2' COMMENT '1已删除2未删除',
  PRIMARY KEY (`version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 正在导出表  svn_version.version 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `version` DISABLE KEYS */;
/*!40000 ALTER TABLE `version` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
