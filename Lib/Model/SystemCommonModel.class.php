<?php
//系统通用模型
class SystemCommonModel extends Model{
	protected $prefix = '';
	
	/**
	 * 通过ID获取配置
	 * @param type $p_id
	 * @return type
	 */
	public function get_data_by_id($p_id)
	{
		$field = $this->prefix .'id';
		
		return $this->where("{$field}='{$p_id}'")->find();
	}
	
	/**
	 * 获取指定的配置列表
	 */
	public function get_data()
	{
		$tmp = $this->select();
		
		$datas = array();
		if(!empty($tmp)){
			foreach($tmp as $val){
				$datas[$val[$this->prefix .'id']] = $val;
			}
		}
		
		return $datas;
	}

	/**
	 * 获取指定的TREE
	 */
	public function get_tree()
	{
		$datas = $this->select();
		if ($datas){
			require_once 'Tree.class.php';
			$tree = new Tree($datas, $this->prefix .'id', $this->prefix .'parent_id');
			return $tree->build_tree();
		}
		
		return array();
	}
	
	/**
	 * 获取指定的一级TREE
	 * @param type $p_company_id
	 * @return type
	 */
	public function get_first_tree(){
		$datas = $this->select();
		if ($datas){
			require_once 'Tree.class.php';
			$tree = new Tree($datas, $this->prefix .'id', $this->prefix .'parent_id');
			return $tree->get_first();
		}
		
		return array();
	}
	
	/**
	 * 创建上级选择器，
	 * 
	 */
	public function create_parent_select($p_defaultId, $p_title='选择区域', $p_idpre='', $class = '')
	{
		$datas = $this->get_tree();
		$html = '<select class="'.$class.'" name="'. $p_idpre.$this->prefix .'parent_id" id="'. $p_idpre.$this->prefix .'parent_id">';
		$html .= '	<option value="">'. $p_title .'</option>';
		
		foreach($datas as $vo){
//			if($vo[$this->prefix .'parent_id'] == 0){
				$html .= '<option value="'. $vo[$this->prefix .'id'] .'" '. ($p_defaultId==$vo[$this->prefix .'id'] ? 'selected' : '') .'>'. $vo['_prefix'] . $vo[$this->prefix .'name'] .'</option>';
//			}
		}
		$html .= '</select>';
		return $html;
	}
	
	/**
	 * 创建选择器，数据来源于数据库
	 */
	public function create_select($p_defaultId, $p_title='选择区域', $p_idpre='', $p_parent=0, $class = '')
	{
		$datas = $this->get_tree();
		$html = '<select class="'.$class.'" name="'. $p_idpre.$this->prefix .'id" id="'. $p_idpre.$this->prefix .'id">';
		$html .= '	<option value="">'. $p_title .'</option>';
		
		foreach($datas as $vo){
			if($p_parent == 1 && $vo[$this->prefix .'parent_id'] > 0){
				continue;
			}
			$html .= '<option value="'. $vo[$this->prefix .'id'] .'" '. ($p_defaultId==$vo[$this->prefix .'id'] ? 'selected' : '') .'>'. $vo['_prefix'] . $vo[$this->prefix .'name'] .'</option>';
		}
		$html .= '</select>';
		return $html;
	}
	
	/**
	 * 创建选择器，数据来源于配置文件
	 */
	public function create_config_select($p_config_name, $p_name_field, $p_title = '', $class = '')
	{
		$datas = C($p_config_name);
		if($datas){
			$html = '<select class="'.$class.'" name="'. $p_name_field .'" id="'. $p_name_field .'">'.PHP_EOL;
			if(!empty($p_title))
				$html .= '<option value="">'. $p_title .'</option>'.PHP_EOL;
			foreach($datas as $key=>$vo){
				$html .= '<option value="'. $key .'" '. ($_GET[$p_name_field] == $key ? 'selected' : '') .'>'. $vo .'</option>'.PHP_EOL;
			}
			$html .= '</select>'.PHP_EOL;
			return $html;
		}else{
			return NULL;
		}
	}
}

?>