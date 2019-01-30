<?php
/**
 * 
 * +------------------------------------------------------------+
 * @category Acl 
 * +------------------------------------------------------------+
 * 后台资源管理类
 * +------------------------------------------------------------+
 *
 * @author anzai <sba3198178@126.com>
 * @copyright http://www.suncco.com 2013
 * @version 1.0
 *
 * Modified at : 2013-7-1 10:29:41
 *
 */

//当前登录用户是否为超级管理员
define('IS_SUPER_LOGIN', (int) session('login_utype') == 1);

class Acl{
	//当前登录用户所属用户组ID
	static $group_id = null;
	
	//系统资源列表
	static private $_resources = null;
	
	//本次操作名
	static $action_name = null;
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name createLink
	 * +------------------------------------------------------------+
	 * 创建一个超链接标签
	 * +------------------------------------------------------------+
	 * @example
	 *
	 * @param string $note 文字
	 * @param string $a
	 * @param string $m
	 * @param mixed $params
	 * @param string $attr
	 * @param boolean $txt 无权限操作时是否显示文字描述
	 *
	 */
	static public function a($note, $a='', $m='', $params='', $attr='', $txt=false){
		if (!self::hasAcl($m, $a)) return $txt ? $note : null;
		$a = empty($a) ? ACTION_NAME : $a;
		$m = empty($m) ? MODULE_NAME : $m;
		$url = U($m.'/'.$a, $params);
		$title = stripos($attr, 'title') !== false ? '' : ' title="'.strip_tags($note).'" ';
		$a = '<a'.$title.' href="'.$url.'" '.$attr.' >'.$note.'</a>';
		
		return $a;
	}
	
	static public function addLink($note, $a='', $m='', $params='', $txt=false, $otherCss = ''){
		$note = '<span class="ico ico-add"></span>' . $note;
		$attr = 'class="mini-btn mini-btn-green '. $otherCss .'"';
		return self::a($note, $a, $m, $params, $attr, $txt);
	}
	
	//获取当前正在操作的资源名称
	static public function getAction($module='', $action='', $identy=''){
		$module = strtolower(empty($module) ? MODULE_NAME : $module);
		$action = strtolower(empty($action) ? ACTION_NAME : $action);
		$key = $module . (empty($identy) ? '' : '_'. $identy ) . '_' . $action;
		$resources = self::$_resources === null ? self::getAcl() : self::$_resources;
		return isset($resources['resource'][$key]) ? $resources['resource'][$key] : '';
	}
	
	/**
	 * 
	 * @param type 模块
	 * @param type 操作
	 * @return boolean true表示有操作权限，false表示没有操作权限
	 */
	static public function hasAcl($module='', $action='', $p_resources=null){
		$module = (empty($module) ? MODULE_NAME : $module);
		$action = (empty($action) ? ACTION_NAME : $action);
		
		if($p_resources !== null){
			self::$_resources = $p_resources;
		}
		
		$resources = self::$_resources;
		
		$key = strtolower($module . '-' . $action);
		if (isset($resources['resources'][$key])){
			self::$action_name = $resources['resources'][$key][0];
			return $resources['resources'][$key][1] == 1 ? true : false;
		}
		
		return true;
	}

	static public function hasView($identy){
		return IS_SUPER_LOGIN || (isset(self::$_permissions[$identy]) && self::$_permissions[$identy]>0) ? true : false;
	}
}

?>