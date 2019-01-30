<?php
/**
 * 
 * 服务器管理
 *
 * @author bluefoot<bluefoot@qq.com>
 */
class ServerAction extends CommonAction {
	
	
	public function __construct()
	{
		parent::__construct();
		
	}


	public function index(){
		$Model = D('Server');
		
		if($this->isPost()){
			$ids	 = !empty($_POST['order_id']) ? $_POST['order_id'] : '';
			
			if(!empty($ids)){
				foreach($ids as $key=>$val){
					$Model->where($Model->getPk()."='{$key}'")->save(array(
						'order_id' => $val
					));
				}
				$this->success('修改排序成功!', 'reload');
			}else {
				$this->error('操作失败!');
			}
		}
		
		
		$list	 = $Model->select();
		
		$this->assign('list', $list);
		$this->display();
	}
	
	public function before_add()
	{
		$server_id = (int)$this->_GET('server_id');
		
		if(!empty($server_id)){
			$server = D('Server')->find($server_id);
			$server['server_id'] = 0;
			$this->assign('data', $server);
		}
	}
	
}

?>