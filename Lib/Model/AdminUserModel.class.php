<?php
/**
 * 管理员管理
 * @author bluefoot<bluefoot@qq.com>
 * @version 2014-06-03
 */
class AdminUserModel extends Model
{

	// 自动验证设置
	protected $_validate = array(
		array('roleid', 'require', '请选择用户组！'),
		array('username', 'require', '用户名不能为空！'),
		array('surename', 'require', '真实姓名不能为空!'),
		array('phone', 'require', '手机号不能为空！'),
	);

	/**
	 * 登录，并设置SESSION
	 * @param type $username
	 * @param type $password
	 * @return int
	 */
	public function login($username, $password) {
		if ( $username && $password ) {
			$data		 = $this->where("username='{$username}' and status=1")->find();
			$password	 = encrypt_pwd($password);
			if ( $data['password'] == $password ) {
				$this->setUserData($data);
				return 1; //登录成功
			} else {
				return 2; //用户名或密码错误
			}
		} else {
			return 3; //用户名和密码不能为空
		}
	}

	function setUserData($data) {
		session('admin_user_id', $data['id']);
		session('admin_user_name', $data['username']);
		session('admin_user_surename', $data['surename']);
		
		session('admin_role_id', $data['role_id']);
		session('admin_warehouse_id', $data['warehouse_id']);
		
		$updata['loginip']	 = get_client_ip();
		$updata['logintime'] = time();
		$this->where("id='" . $data['id'] . "'")->save($updata);
	}
}

?>