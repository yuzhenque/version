<?php

/**
 * ------------------------------------
 * 公用资源，如图文，图片
 *
 * @author Bluefoot. 2013-11-25
 * ------------------------------------
 */
class WxSourceWidget extends CommonAction
{
	/**
	 * 获取图文模块
	 * 
	 * @param string $p_from  由哪边获取['menu', 'focus_on', 'focus_out', 'no_keywords']
	 */
	public function index($p_from, $p_parentId=0, $type=1, $p_edit=1)
	{
		//提取图文时封面
		$coverArr	 = D('WxSource')->where("boss_id='{$this->_boss_id}' AND wxso_from='{$p_from}' AND wxso_parent_id='{$p_parentId}' AND wxso_is_cover='1'")->find();
		//提取其它图文列表
		$newList	 = D('WxSource')->where("boss_id='{$this->_boss_id}' AND wxso_from='{$p_from}' AND wxso_parent_id='{$p_parentId}' AND wxso_is_cover='0'")->select();
		
		
		$this->assign('from', 		$p_from);
		$this->assign('coverArr', 	$coverArr);
		$this->assign('newList', 	$newList);
		$this->assign('parentId', 	(int)$p_parentId);
		$this->assign('type', 		$type);
		$this->assign('source_edit', 		$p_edit);
		$this->display("./Lib/Widget/Tpl/WxSource/index.html");
	}

}

?>