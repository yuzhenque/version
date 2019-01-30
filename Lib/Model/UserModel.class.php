<?php

/**
 * 用户模型
 * 
 * @author bluefoot<bluefoot@qq.com>
 * @version 2014-06-05
 */
class UserModel extends Model {

	protected $_validate = array(
//		array('user_username', '/^([a-zA-Z])+([a-zA-Z0-9_\.\-\@])+([a-zA-Z0-9])+$/i', '用户名格式错误', 0),
//		array('user_username', '', '该昵称已被使用', 2, 'unique'),
//		array('user_password', 'require', '密码不能为空', 0),
		array('password', '6,20', '密码长度错误，必须是6-20', 2, 'length'),
		array('repassword', 'password', '确认密码不正确', 2, 'confirm')
	);

	/**
	 * 登录成功设置session
	 * 
	 * @param array $user
	 * @return ;
	 */
	public function setData($user) {
//		session('user_id', $user['user_id']);
//		session('user_username', $user['user_username']);

		//登陆成功，写入COOKIE
		$usercode = authcode($user['user_id'] . '\t' . $user['user_password'] . '\t' . time(), false);
		cookie('usercode', $usercode, "expire=". time() + 30*86400);
		
		// 更新用户状态
		$data['user_last_login_time'] = time();
		$data['user_last_login_ip']	 = get_client_ip();
		$data['user_login_count'] = array('exp', 'user_login_count+1'); // 登录次数加 1
		D("User")->where('user_id=' . $user['user_id'])->save($data);
		
		//更新购物车
		if(!empty($user['user_openid'])){
			D('Cart')->where("cart_openid='{$user['user_openid']}'")->save(array(
				'cart_openid' => $user['user_open_id'],
				'cart_user_id' => $user['user_id']
			));
		}
		
		return $usercode;
	}

	/**
	 * 获取指定用户名的用户基本信息
	 * 
	 * @param Int	$p_name	用户名
	 * @return Array
	 */
	public function getBasic($p_id) {
		$userArr = $this->where("user_id='{$p_id}'")->find();
		return $userArr;
	}

	/**
	 * 获取指定用户名的用户基本信息
	 * 
	 * @param Int	$p_name	用户名
	 * @return Array
	 */
	public function getBasicByName($p_name) {
		$userArr = $this->where("user_username='{$p_name}'")->find();
		return $userArr;
	}


	/**
	 * 登录用户
	 * 
	 * @param String $p_username	用户名
	 * @param String $p_password	用户密码
	 * 
	 * @return int [0成功1未填写完整2用户不存在3密码错误]
	 */
	public function login($p_name, $p_pwd) {
		if (empty($p_name) || empty($p_pwd)) {
			return -1;
		}

		//进行判断
		$cond['user_username']	 = $p_name;
		$cond['user_phone']		 = $p_name;
		$cond['user_email']		 = $p_name;
		$cond['_logic']		 = 'or';
		$userArr			 = $this->where($cond)->find();
		
		if (empty($userArr)) {
			return -2;
		}
		if ($userArr['user_password'] != encrypt_pwd($p_pwd)) {
			return -3;
		}
		if($userArr['user_status'] != 1){
			return -4;
		}

		//保存数据
		$this->setData($userArr);

		return true;
	}

	/**
	 * 退出登录时清除信息动作
	 */
	public function logout() {
		session('user_id', null);
		session('user_username', null);
		session('verify', null);
		session_destroy();
		
		cookie('usercode', null, "expire=0");
	}

	/**
	 * 登录错误消息
	 * @param string $error_id 错误代码
	 * @author Taylor <lixj@suncco.com>
	 */
	public function login_error($error_id){
		switch ($error_id) {
			case -1:
				return '手机号码不存在';
			case -2:
				return '用户名不存在';
			case -3:
				return '密码错误';
			case -4:
				return '账号被锁定';
			default:
				return '错误类型未知';
		}
	}
	
	/**
	 * 自动升级
	 */
	public function auto_level($p_userId)
	{
		if(empty($p_userId)){
			return false;
		}
		$config = C('MEMBER_LEVEL_CREDIT');
		
		$user = D('User')->where("user_id='{$p_userId}'")->find();
		
		$level = 1;
		for($i=5; $i<=1; $i--){
			if($user['user_total_credit'] >= $config[$i]){
				$level = $i;
				break;
			}
		}
		if($level > $user['user_level']){
			D('User')->where("user_id='{$p_userId}'")->save(array(
				'user_level' => $level
			));
		}
		return true;
	}
	
	
	/**
	 * 获取登录后的跳转地址
	 */
	public function set_referer()
	{
		//获取上一页地址
		$referer_url = $_SERVER['HTTP_REFERER'];
		if(strpos($referer_url, 'login') !== false || strpos($referer_url, 'logout') !== false ||
			strpos($referer_url, 'register') !== false || strpos($referer_url, 'pwd') !== false ||
			strpos($referer_url, 'add') !== false || strpos($referer_url, 'edit') !== false ||
			strpos($referer_url, 'delete') !== false){
			return cookie('referer_url');
		}
		cookie('referer_url', $referer_url);
		return $referer_url;
	}
	/*
	*  获取user信息
	*/
	public function getUserInfo($user_id = '',$field = ""){
		$user_model 		= D('User');
		$where['user_id']	= $user_id;
		$filed 				= $field ;
		if($filed){
			$user_info		= $user_model->where($where)->getField($filed);
		}
		else{
			$user_info		= $user_model->where($where)->field($filed)->find();
		}
		
		return $user_info;
	}
	/*
	 * 获取用户的省份城市
	 * 输入  $province_id   省份ID
	 * 输入  $city_id 		城市ID
	 * 返回  省份和城市名称拼接
	*/
	public function getUserArea($province_id = "", $city_id = ""){
		if(!empty($city_id))
		{
			$city_name 	   = D("Area")->where(array('ar_id'=>$city_id))->getField('ar_name');
		}
		if(!empty($province_id)){
			$province_name = D("Area")->where(array('ar_id'=>$province_id))->getField('ar_name');
		}

		return $province_name . " " . $city_name;
	}

	/*
	 * 获取供应商类型
	 * 输入 		$user_role_type  类型字符串（系列化数据）
	*/
	public function getRoleType($user_role_type = ""){

		$role_name 	= "";
		if(!empty($user_role_type)){
			$role_id 	= unserialize($user_role_type);
			$role_type 	= C("ROLE_TYPE");
			$result = array();
			foreach ($role_id as $key => $value) {
				array_push($result, $role_type[$value]);
			}
		}

		return implode("、", $result);
	}
}

?>