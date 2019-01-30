<?php

class AdminLogModel extends Model{
	
	/**
	 * 写入日志信息
	 */
	public function write($descript=''){
		if((MODULE_NAME != 'AdminLog' && !empty($_SESSION['admin_user_id']))){
			$method = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest' ? 'ajax' :strtolower($_SERVER['REQUEST_METHOD']);  //获取请求方法
			$method = $method == 'get' ? 1 : ($method == 'post' ? 2 : ($method=='ajax'? 3: 0));
			if($descript){
				foreach($descript as $rs){
					$title .= $rs['title']."-";
				}
				$title = str_replace('--','',$title.'-');
			}
			
			$data['username'] = $_SESSION['admin_user_name'];
			$data['descript'] = $title;
			$data['request_type'] = $method;
			$data['url'] = __SELF__;
			$data['ip'] = get_client_ip();
			$data['careate_time'] = date("Y-m-d H:i:s");
			$this->add($data);
		}
	}
}
?>