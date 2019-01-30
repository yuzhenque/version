<?php

/**
 * 更新管理
 */
class VersionCommonAction extends CommonAction
{

	/**
	 * 类型ID
	 */
	protected $_type_id = 1;
	/**
	 * 最后过滤的文件
	 */
	protected $_last_filter = '';

	public function index()
	{
		parent::index('', 'Version:index', 'Version', "version_type_id='{$this->_type_id}' AND is_delete=2");
	}

	/**
	 * 添加栏目 
	 */
	public function add()
	{
		if ($this->isPost()) {
			$version_name	 = $this->_POST('version_name');
			$version		 = D('Version')->where("version_name='{$version_name}' AND version_type_id='{$this->_type_id}' AND is_delete=2")->find();
			if (!empty($version)) {
				$this->error("版本名已被使用");
			}
			$_POST['version_type_id']		 = $this->_type_id;
			$_POST['version_plan_time']		 = strtotime($_POST['version_plan_time']);
			$_POST['version_release_time']	 = strtotime($_POST['version_release_time']);
			$_POST['version_file']			 = $this->_filter_file($_POST['version_file'], $_POST['version_file_not_exsits']);
			$_POST['version_file_filter']	 = $this->_last_filter;
		}
		parent::add('Version');
	}

	/**
	 * 添加或修改栏目
	 */
	public function edit()
	{
		if ($this->isPost()) {
			$version_id		 = $this->_GET('version_id');
			$version_name	 = $this->_POST('version_name');
			$this->_last_filter = $this->_POST('version_file_filter');
			$version		 = D('Version')->where("version_id!='{$version_id}' AND version_name='{$version_name}' AND version_type_id='{$this->_type_id}' AND is_delete=2")->find();
			if (!empty($version)) {
				$this->error("版本名已被使用");
			}
			$_POST['version_plan_time']		 = strtotime($_POST['version_plan_time']);
			$_POST['version_release_time']	 = strtotime($_POST['version_release_time']);
			$_POST['version_file']			 = $this->_filter_file($_POST['version_file'], $_POST['version_file_not_exsits']);
			$_POST['version_file_filter']	 = $this->_last_filter;
		}

		$_POST['referer'] = U(MODULE_NAME.'/edit', 'version_id='. $version_id);
		parent::edit('Version');
	}

	/**
	 * 删除栏目
	 */
	public function delete()
	{
		$version_id		 = $this->_GET('version_id');
		if(empty($version_id)){
			$this->error("ERROR");
		}
		
		D('Version')->where("version_id='{$version_id}'")->save(array(
			'is_delete' => 1
		));
		
		$this->success('删除成功!', 'reload');
	}
	
	/**
	 * 过滤文件
	 * 
	 * @param type $p_file
	 * @param type $p_filter
	 * @return string
	 */
	private function _filter_file($p_file, $p_filter)
	{
		if(empty($p_file)){
			return '';
		}
		
		$p_file = $this->_unique_string($p_file);
		
		if(empty($p_filter)){
			return $p_file;
		}
		
		$p_filter = $this->_unique_string($p_filter);
		
		
		$file_list = explode("\n", $p_file);
		$filter_list = explode("\n", $p_filter);
		
		foreach($file_list as $k1=>$v1){
			foreach($filter_list as $v2){
				if(strpos($v1, $v2) !== false){
					$this->_last_filter .= $v1 ."\n";
					unset($file_list[$k1]);
				}
			}
		}
		
		return implode("\n", $file_list);
	}
	
	/**
	 * 字符串唯一
	 * 
	 * @param type $p_str
	 * @return type
	 */
	private function _unique_string($p_str)
	{
		$p_str = str_replace(array(chr(13), "\s\n", "\n"), '|', $p_str);
		$p_str = str_replace('|||', '|', $p_str);
		$p_str = str_replace('||', '|', $p_str);
		
		$p_str = array_unique(explode('|', $p_str));
		return implode("\n", $p_str);
	}

}

?>