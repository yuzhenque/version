<?php
/**
 * 
 * +------------------------------------------------------------+
 * @category Tree 
 * +------------------------------------------------------------+
 * 对一组数据进行上下级归类排序，形成一棵树
 * +------------------------------------------------------------+
 *
 * @author anzai <sba3198178@126.com>
 * @copyright http://www.suncco.com 2013
 * @version 1.0
 *
 * Modified at : 2013-6-8 09:20:55
 *
 */
class Tree {
	///生成树型结构所需要的2维数组
	private $_data;
	
	///主键字段名
	private $_pk = 'id';
	
	///关联上级字段名
	private $_pfield = 'pid';
	
	///表示一级节点的对应上级字段对应的值
	private $_pvalue = 0;
	
	private static $_icon = array('┌','├','└','─','　　','│');
	
	//递归得到的枝叶是否增加起止符
	public $mark = false;
	
	public function __construct($data = array(), $pk = 'id' ,$pfield = 'pid' ,$pvalue = '0'){
		$this->_data	= $data;
		$this->_pk		= $pk;
		$this->_pfield	= $pfield;
		$this->_pvalue	= $pvalue;
	}
	
	//构建一棵完整的树
	public function build_tree($mark = false, $setPrefix = true){
		$this->mark = $mark;
		$parent_nodes = $this->get_first();
		$total = count($parent_nodes);
		$tree = array();
		
		if ($total > 0){
			$idx = 1;
			foreach ($parent_nodes as $vo){
				$setPrefix && $vo['_prefix'] = ($total > 1 ? ($idx == 1 ? self::$_icon[0] : ($idx==$total ? self::$_icon[2] : self::$_icon[1])) : '').self::$_icon[3].' ';
				$child = $this->get_tree($vo[$this->_pk] ,1, $setPrefix);
				$vo['_has_child'] = empty($child) ? false : true;
				$tree[] = $vo;
				$vo['_has_child'] && $tree = array_merge_recursive($tree, $child);
				unset($child);
				$idx ++;
			}
		}
			
		//将未归类节点进行归类
		if (!empty($this->_data)){
			$tree = $tree + $this->_data;
			$total += count($this->_data);
			$idx = 1;
			foreach ($tree as $key=>$vo){
				if (!isset($vo['_grade']) || $vo['_grade'] == 0){
					$tree[$key][$this->_pfield] = 0;
					$tree[$key]['_grade'] = 0;
					$tree[$key]['_has_child'] = false;
					$setPrefix && $tree[$key]['_prefix'] = ($total > 1 ? ($idx == 1 ? self::$_icon[0] : ($idx==$total ? self::$_icon[2] : self::$_icon[1])) : '').' ';
					$idx ++;
				}
			}
		}
		
		return $tree;
	}
	
	//构建获取指定节点$id下的一棵子树
	public function get_tree($id, $grade=1, $setPrefix = true){
		$child_notes = $this->get_child($id, $grade);
		$total = count($child_notes);
		$tree = array();
		if ($total > 0) {
			$this->mark && $tree[] = '_START_';
			$idx = 1;
			$sufixStr = repeat(self::$_icon[3], $grade, true, '').' ';
			$preStr = repeat(self::$_icon[4], $grade, true, '');
			foreach ($child_notes as $vo){
				$setPrefix && $vo['_prefix'] = $preStr.($total > 1 ? ($idx == 1 ? self::$_icon[0] : ($idx==$total ? self::$_icon[2] : self::$_icon[1])) : '') . $sufixStr;
				
				$child = $this->get_tree($vo[$this->_pk], $grade+1);
				$vo['_has_child'] = empty($child) ? false : true;
				$tree[] = $vo;
				$vo['_has_child'] && $tree = array_merge_recursive($tree, $child);
				unset($child);
				$idx ++;
			}
			$this->mark && $tree[] = '_END_';
		}
		
		return $tree;
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name get_child
	 * +------------------------------------------------------------+
	 * 根据指定节点ID获取其子节点信息
	 * +------------------------------------------------------------+
	 *
	 * @param int $id	指定节点ID
	 * @param int $grade 级别
	 * @param boolean $rec 是否递归获取子节点信息
	 *
	 */
	public function get_child($id, $grade=1, $rec = false){
		$child = array();
		foreach ($this->_data as $k => $v){
			if ($v[$this->_pfield] == $id){
				$v['_grade'] = $grade;
				$child[] = $v;
				
				//递归获取子节点信息
				if ($rec) $child = array_merge_recursive($child, $this->get_child($v[$this->_pk], $grade+1, $rec));
				
				unset($this->_data[$k]);
			}
		}
		return $child;
	}
	
	//获取第一级节点信息
	public function get_first(){
		$first = array();
		
		foreach ($this->_data as $key => $vo){
			if ($vo[$this->_pfield] == $this->_pvalue){
				//添加级别信息，0表示第一级
				$vo['_grade'] = 0;
				$first[] = $vo;
				
				unset($this->_data[$key]);
			}
		}
		
		return $first;
	}
}

?>