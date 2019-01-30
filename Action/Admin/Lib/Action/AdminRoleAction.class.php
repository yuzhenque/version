<?php

/**
 * 角色管理
 * 
 * @author bluefoot<bluefoot@qq.com>
 * @version 2014-06-04
 */
class AdminRoleAction extends CommonAction
{

	/**
	 * 角色列表
	 */
	public function index()
	{
		parent::index("id ASC");
	}

}

?>