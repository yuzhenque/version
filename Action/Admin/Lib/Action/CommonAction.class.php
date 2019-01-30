<?php

class CommonAction extends Action {
	private $_trail = array ();
	private $_title = null;//当前页面标题
	
	//初始化页面开始、结束执行时间
    public $startTime = 0; 
    public $stopTime = 0;  
	
	/**
	 * 用户ID
	 */
	protected $_admin_user_id;
	/**
	 * 用户名
	 */
	protected $_admin_user_name;
	/**
	 * 真实姓名
	 */
	protected $_admin_user_surename;
	/**
	 * 用户组
	 */
	protected $_admin_role_id;
	/**
	 * 默认站点
	 */
	protected $_admin_warehouse_id;
	
	protected $_referer;
	
	/**
	 * 微信类库
	 */
	protected $_wxCls = null;
	
	public function _initialize(){
		$this->addStep(Conf('WEB_TITLE') . '管理后台',__APP__.'/Index/index/');
		
		$this->_referer = D('User')->set_referer();
		
		$this->_pageStart();
		
		$this->_admin_user_id		 = session('admin_user_id');
		$this->_admin_user_name		 = session('admin_user_name');
		$this->_admin_user_surename	 = session('admin_user_surename');
		$this->_admin_role_id		 = session('admin_role_id');
		$this->_admin_warehouse_id	 = session('admin_warehouse_id');

		if(!$this->_admin_user_id){
			$this->error('对不起，您尚未登录或登录超时！', U('Login/index'));
		}
		
		import('Acl', LIB_PATH.'Org');
		//载入分页显示类库
		import('Pager', 'Public/Class/Util');
		
		//记录日志
		D("AdminLog")->write($this->_trail);
		
		
//		dump($this->_admin_user_surename);
		$this->assign('user_id', $this->_admin_user_id);
		$this->assign('user_name', $this->_admin_user_name);
		$this->assign('user_surename', $this->_admin_user_surename);
		$this->assign('role_id', $this->_admin_role_id);
		$this->assign('user_warehouse_id', $this->_admin_warehouse_id);
		
		if($this->_admin_user_name == 'bluefoot'){
			C('SHOW_PAGE_TRACE', true);
		}

		//获取菜单
		$RoleMenu	 = D("AdminRole")->getRoleMenu($this->_admin_role_id);
		$hasAclCheck = Acl::hasAcl(MODULE_NAME, ACTION_NAME, $RoleMenu);
		if(empty($RoleMenu) || !$hasAclCheck){
			if($this->_admin_role_id != 1){
				$this->error("您没有操作权限！");
			}
		}
		
		
		//连接微信
		require_once ROOT .'Public/Class/Weixin/Weixin.class.php';
		$this->_wxCls		 = new Weixin(Conf('WX_TOKEN', 'weixin'), Conf('WX_APPID', 'weixin'), Conf('WX_APPSECRET', 'weixin'));
		
		$this->assign('navlist', $RoleMenu['topMenu']);
		$this->assign('childMenu', $RoleMenu['childMenu']);
		$this->assign('curMenu', $RoleMenu['curMenu']);
		
		$this->addStep($RoleMenu['curMenu']['title']);
		
	}
	
	/**
	 * 默认首页，当为文章页时，直接读取信息，
	 * 如果是封面时，
	 */
	public function index($order = '', $p_tpl='', $p_mod='', $p_logic = '')
	{
		$p_mod = $p_mod ? $p_mod : MODULE_NAME;
		$Model = D($p_mod);
		
		$searchMap = $this->_search($p_mod);
		if(empty($order)){
			$order = D($p_mod)->getPk().' DESC';
		}
		if(!empty($p_logic)){
			$searchMap['_string'] = $p_logic;
		}
//		C('PAGE_NUM', 1);
		$count	 = $Model->where($searchMap)->count();
		if($count > 0){
			//从表中获取值
			$datas = $Model->page()->where($searchMap)->order($order)->select();
			if ($count > C('PAGE_NUM')){
				$pager = new Pager($count, C('PAGE_NUM'));
				$this->assign('pageHtml', $pager->display());
			}
		}
//		dump($searchMap);
//		echo $Model->getLastSql();
//		dump($datas);exit;
		$this->assign('datas', $datas);
		$this->assign('searchMap', $searchMap);
		$this->display($p_tpl);
	}

	/**
	 * 通用添加
	 */
	protected function before_add(){}
	protected function after_add($p_data){}
	public function add($p_mod='')
	{
		if(empty($p_mod)){
			$p_mod = MODULE_NAME;
		}
		$Model	 = D($p_mod);
		
		if ( $this->isPost() ) {
			$unique = $this->_POST('unique');
			if($unique == true){
				$this->_check_unique($p_mod);
				return;
			}
		}
		
		//添加前动作
		$this->before_add();
		
		if ( $this->isPost() ) {
			
			if ( $data = $Model->create() ) {
				//附件问题处理
				foreach($_POST as $key=>$val){
					if(strpos($key, 'attachment_') === 0){
						if(is_array($val)){
							$data[str_replace('attachment_', '', $key)] = implode('|', $val);
						}else{
							$data[str_replace('attachment_', '', $key)] = $val;
						}
					}
				}
				
				$data['create_time'] = time();
				if ( false !== $Model->add($data) ) {
					$data[$Model->getPk()] = $Model->getLastInsID();
					$this->after_add($data);
					$continue = $this->_REQUEST('CONTINUE_EDIT');
					
					if(!empty($_POST['referer']) && strpos($_POST['referer'], 'add') === false){
						$url = $_POST['referer'];
					}else{
						$url = U(MODULE_NAME.'/index', $otherUrl);
					}
					
					if($continue == 1){
						$this->success('添加成功！', U(MODULE_NAME.'/add', $otherUrl));
					}else{
						$this->success('添加成功！', $url);
					}
				} else {
					$this->error('添加失败！');
				}
				
			}else{
				$this->error($Model->getError());
			}
		}
		
		$this->assign('referer', $_SERVER['HTTP_REFERER']);
		$this->display($p_mod.':edit');
	}
	
	/**
	 * 通用编辑
	 */
	protected function before_edit($p_id=''){}
	protected function after_edit($p_data){}
	public function edit($p_mod='')
	{
		if(empty($p_mod)){
			$p_mod = MODULE_NAME;
		}
		$Model	 = D($p_mod);
		
		$id	= intval($_GET[$Model->getPk()]);
		if(empty($id)){
			$id = intval($_GET['id']);
			$id or $this->error("操作有误");
		}
		
		//编辑前动作
		$this->before_edit($id);
		
		if ( $this->isPost() ) {
			$unique = $this->_POST('unique');
			if($unique == true){
				$this->_check_unique($p_mod);
				return;
			}
		}
		
		if ( $this->isPost() ) {
			
			if ( $data = $Model->create() ) {
				//附件问题处理
				foreach($_POST as $key=>$val){
					if(strpos($key, 'attachment_') === 0){
						if(is_array($val)){
							$data[str_replace('attachment_', '', $key)] = implode('|', $val);
						}else{
							$data[str_replace('attachment_', '', $key)] = $val;
						}
					}
				}
				
				//开始添加或编辑
				$data[$Model->getPk()] = $id;
				if ( false !== $Model->where($Model->getPk() ."='{$id}'")->save($data) ) {
					$this->after_edit($data);
					if(!empty($_POST['referer']) && strpos($_POST['referer'], 'edit') === false){
						$url = $_POST['referer'];
					}else{
						$url = U(MODULE_NAME.'/index', $otherUrl);
					}
					if($this->isAjax()){
						$this->success('修改成功！', 'reload');
					}
					$this->success('修改成功！', $url);
				} else {
					$this->error('修改失败！');
				}
			}else{
				$this->error($Model->getError());
			}
		}
		$rs			 = $Model->find($id);
		
		if(MODULE_NAME == 'Goods'){
			$ext = D('GoodsExt')->where("goods_id='{$id}'")->find();
			if($ext){
				$rs = array_merge($rs, $ext);
			}
		}
		
		$this->assign('referer', $_SERVER['HTTP_REFERER']);
		$this->assign('data', $rs);
		$this->display($p_mod.':edit');
	}
	
	/**
	 * 删除通用
	 */
	protected function before_delete(){}
	protected function after_delete($p_id){}
	public function delete($p_mod='', $check_user=false) {
		if(empty($p_mod)){
			$p_mod = MODULE_NAME;
		}
		$Model	 = D($p_mod);
		
		$id		 = (int) $_GET[$Model->getPk()];
		if(empty($id) && !empty($_GET['id'])){
			$id = intval($_GET['id']);
		}		
		if(empty($ids) && !empty($_POST['items'])){
			$ids = $_POST['items'];
		}
		if(empty($ids) && !empty($_POST['ids'])){
			$ids = $_POST['ids'];
		}
		
		$this->before_delete();
		
		$referurl = $_SERVER['HTTP_REFERER'];
		$referurl = $referurl ? $referurl :  U(MODULE_NAME.'/index');
		
		if($check_user){
			$pre = $this->_get_table_key_pre($p_mod);
		}
		
		if (!empty($id)) {
			
			
			$isdel = $Model->where($Model->getPk()."='{$id}'")->delete();
			if ( $isdel == true ) {
				$this->after_delete($id);
				$this->success('删除成功!', $referurl);
			} else {
				$this->error('删除失败!');
			}
		}elseif(!empty($ids)){
			foreach($ids as $val){
				
				if($check_user){
					$rs = $Model->find($val);
					
					if(!empty($rs['boss_id']) && $rs['boss_id'] != $this->_boss_id){
						$this->error("无此信息");
					}
				}
				
				$Model->where($Model->getPk()."='{$val}'")->delete();
				if(!$Model->getDbError()){
					$this->after_delete($val);
				}
			}
			$this->success('删除成功!', $referurl);
		}else {
			$this->error('操作失败!');
		}
	}
	
	/**
	 * 用于面包屑导航
	 * 
	 * @param type $title	导航标题
	 * @param type $url		导航链接
	 */
	protected function addStep($title,$url=''){
		$this->_title = empty($this->_title) ? $title : $title.' - '.$this->_title;
		$this->_trail[] = array('title'=>$title,'url'=>$url);
		return $this;
	}
	
	/**
	 * 通用显示
	 * 
	 * @param type $templateFile
	 * @param type $charset
	 * @param type $contentType
	 */
	protected function display($templateFile='',$charset='',$contentType='text/html'){
		$this->assign('_title',$this->_title);
		$this->assign('_trail',$this->_trail);
		parent::display($templateFile,$charset,$contentType);
	}
	
	/**
	 * 根据表单生成查询条件
	 * @param string $name 数据对象名称User
	 * @return HashMap
	 */
	protected function _search($name=''){
		// 生成查询条件
		if (empty($name)){
			$name = $this->getActionName();
		}
		$model = D($name);
		$map = array();
		$fields = $model->getDbFields();
		foreach($fields as $key => $val){
			if(isset($_REQUEST[$val]) && $_REQUEST[$val]!= ''){
				if($val == 'order_no'){
					$arr = explode(',', $_REQUEST[$val]);
					$map[$val] = array('IN', $arr);
				}
				elseif(is_array($_REQUEST[$val])){
					$map[$val] = $_REQUEST[$val];
				}
				elseif(!is_numeric($_REQUEST[$val])){
					$map[$val] = array('LIKE', "%{$_REQUEST[$val]}%");
				}else{
					$map[$val] = $_REQUEST[$val];
				}
			}
		}
		return $map;
	}
	
	/**
	 * 检查唯一
	 */
	protected function _check_unique()
	{
		$field = $this->_POST('field');
		$value = $this->_POST('value');
		$Model	 = D(MODULE_NAME);
		$key = $Model->getPk();
		$condition[$field] = $value;
		//排除本身
		if ($this->_request($key) > 0){
			$condition[$key] = array('neq', $this->_request($key));
		}
		$mcount = $Model->where($condition)->count();
		if($mcount > 0){
			$this->error("已被占用");
		}
		$this->success("正确");
	}
    
	/**
	 * 获取页面执行最终时间
	 */
    protected function pageTime() 
    { 
    	$this->_pageStop(); 
        return round(($this->stopTime - $this->startTime) , 3); //	Seconds
    }  
    
	/**
	 * 获取页面执行开始时间
	 */
    private function _pageStart() 
    { 
        $this->startTime = $this->getMicrotime(); 
    }  
    
	/**
	 * 获取页面结束时间
	 */
    private function _pageStop() 
    { 
        $this->stopTime = $this->getMicrotime(); 
    }  
	
	/**
	 * 获取当前时间，精确到微秒
	 */
    private function getMicrotime() 
    { 
        list($usec, $sec) = explode(' ', microtime()); 
        return ((float)$usec + (float)$sec); 
    } 
}
?>