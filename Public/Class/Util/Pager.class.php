<?php
/**
 * 
 * +------------------------------------------------------------+
 * @category Page
 * +------------------------------------------------------------+
 * 根据自定义规则生成分页
 * +------------------------------------------------------------+
 *
 * @author anzai <sba3198178@126.com>
 * @copyright http://www.suncco.com 2013
 * @version 1.0
 *
 * Modified at : 2013-2-25 15:52:15
 *
 */
class Pager{
	
	public $totalPages;
	
	///分页显示格式
	private $_format = '%total%first%prev%list%next%last%normal';
	
	///分页变量名
	private $_var = 'p';
	
	///数据总条数
	private $_count;
	
	///每次显示几条
	private $_listnum = 15;
	
	///分栏数目
	private $_column = 5;
	
	///分页a链接标签自定义class
	private $_className = array();
		
	private $_config = array(
		'record' => '条记录',
		'page' => '',
		'prev' => '上页',
		'next' => '下页',
		'first'=> '第一页',
		'last' => '最后一页'
	);
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name __construct
	 * +------------------------------------------------------------+
	 * 构造函数
	 * +------------------------------------------------------------+
	 * 
	 * @param int $count  数据总条数
	 * @param int $list	     每页显示条数
	 * @param int $column 分栏数
	 *
	 */
	public function __construct($count, $list = 15, $column = 5){
		$this->_count = $count;
		$this->_listnum = (int)$list>0 ? (int) $list : 15;
		$this->_column = $column;
	}
	
	//添加className
	public function addClassName($className){
		$this->_className[] = $className;
		
		return $this; 
	}
	
	//设置显示格式
	public function format($format){
		if(!empty($format)) $this->_format = $format;
		
		return $this;
	}
	
	public function config($config){
		if (!empty($config)){
			$this->_config = extend($this->_config, $config);
		}
		
		return $this;
	}
	
	public function display(){
		$list = $this->_listnum;
		$count = $this->_count;
		if ($count <= $list) return;

		$var = $this->_var;
		$this->totalPages = $totalPages = ceil($count/$list); //总页数
		
		$nowPage = min($totalPages, max(1, (int) $_GET[$var]));//当前页;
		$prev = $nowPage > 1 ? $nowPage - 1 : 1;//上一页
		$next = $nowPage < $totalPages ? $nowPage + 1 : $totalPages;//下一页
		
		$className = empty($this->_className) ? '' : ' ' . implode(' ', $this->_className);
		
		$prevHtml  = $nowPage <= 1 ? '' : '<a title="上一页" class="PAGER_PREV' . $className . '" href="' . $this->_url($prev) .'">'.$this->_config['prev'].'</a>';
		$nextHtml  = $nowPage >= $totalPages ? '' : '<a title="下一页" class="PAGER_NEXT' . $className . '" href="' .  $this->_url($next) .'">'.$this->_config['next'].'</a>';
		$firstHtml = $nowPage > 1 ? '<a title="第一页" class="PAGER_FIRST' . $className . '" href="'. $this->_url(1) . '">'.$this->_config['first'].'</a>' : '';
		$lastHtml  = $nowPage < $totalPages ? '<a title="最后一页" class="PAGER_LAST' . $className . '" href="'. $this->_url($totalPages) .'">'.$this->_config['last'].'</a>' : '';
		
		$listHtml = '';
		
		if ($totalPages > $this->_column){
			$start = $nowPage <= 1 ? 1 : ($nowPage - 1);
			$end   = $start + $this->_column - 1;
			$end   = $end > $totalPages ? $totalPages : $end;
			$start = $end - $start < $this->_column ? $end - $this->_column + 1 : $start;
			$start = $start <=0 ? 1 : $start;
		}else{
			$start = 1;
			$end   = $totalPages;
		}
		
		
		for ($i = $start; $i <= $end; $i++){
			$listHtml .= $i==$nowPage ? '<a class="PAGER_CURRENT">'.$i.'</a>' : '<a class="PAGER_ITEM' . $className . '" href="' .  $this->_url($i) .'">'.$i.'</a>';
		}
		
		//自定义显示页数
		$normalHtml = '<select onchange="document.location.href=this.value" class="PAGER_NORMAL">';
		for ($i = 1; $i <= $totalPages; $i++){
			$normalHtml .= '<option value="' .  $this->_url($i) . '"' . ($i == $nowPage ? ' selected' : '') . '>' . $i . '</option>';
		}
		$normalHtml .= '</select>';
		
		$pageHtml = str_ireplace(
			array('%total', '%prev', '%next', '%list', '%first', '%last', '%normal'), 
			array('<span class="PAGER_TOTAL">' .$count . $this->_config['record'] . '</span>', $prevHtml, $nextHtml, $listHtml, $firstHtml, $lastHtml, $normalHtml), 
			$this->_format
		);
		unset($count, $totalPages, $nowPage, $prevHtml, $nextHtml, $listHtml, $firstHtml, $lastHtml, $normalHtml);
		return $pageHtml;
	}
	
	private function _url($page){
		static $params = null;
		if($_GET['_URL_']){
			unset($_GET['_URL_']);
		}
		if($_GET['tb_title']){
			unset($_GET['tb_title']);
		}
		if ($params === null){
			$params = extend($_GET, $_POST);
		}
		$params = extend($params, array($this->_var => $page));

		//处理已数组方式传参
// 		$params_new = array();
// 		if(!empty($params)){
// 			foreach ($params as $k=>$v){
// 				if(is_array($v)){
// 					foreach ($v as $key=>$val){
// 						$params_new[$k."[".$key."]"] = $val;
// 					}
// 				}else{
// 					$params_new[$k] = $v;
// 				}
// 			}
// 		}else{
// 			$params_new = $params;
// 		}
// 		dump($params_new);
// 		exit;
		return U(MODULE_NAME.'/'.ACTION_NAME, $params);
	}
}

?>