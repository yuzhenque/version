<?php
/**
 * 更新管理
 */
class WwwVersionAction extends VersionCommonAction
{
	public function __construct()
	{
		parent::__construct();
		
		$this->_type_id = 2;
	}
}

?>