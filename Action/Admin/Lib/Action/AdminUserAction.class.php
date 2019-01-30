<?php

/**
 * 管理员管理
 * 
 * @author bluefoot<bluefoot@qq.com>
 * @version 2014-06-04
 */
class AdminUserAction extends CommonAction
{
	public function __construct() {
		parent::__construct();
		
	}
	
	public function index() {
		$page	 = $_GET['p'] ? isNum($_GET['p']) : 1;
		$Model	 = D("AdminUser");
		$count	 = $Model->count();
		import("ORG.Util.Page"); //导入分页类
		$p		 = new Page($count, 10);
		$list	 = $Model->field("admin_user.*, admin_role.title")->join("admin_role On role_id=admin_role.id")->limit($p->firstRow . ',' . $p->listRows)->order('admin_user.id desc')->select();
		$this->assign('list', $list);
		$this->assign('page', $p->show());
		$this->display();
	}
	
	protected function before_add()
	{
		$this->assign('RoleList', D('AdminRole')->select());
		if($this->isPost()){
			$username	 = $this->_POST('username');
			$count		 = D('AdminUser')->where("username='{$username}'")->count();
			if($count > 0){
				$this->error("该用户已存在");
			}
			$_POST['addtime'] = time();
			$_POST['password'] = encrypt_pwd($_POST['password']);
		}
	}

	protected function before_edit()
	{
		$this->assign('RoleList', D('AdminRole')->select());
		if($this->isPost()){
			if(!empty($_POST['password'])){
				$_POST['password'] = encrypt_pwd($_POST['password']);
			}else{
				unset($_POST['password']);
			}
		}
	}
	
	function Lock() {
		$id = isNum($_GET['id']);
		if ( $id == $this->UserID ) {
			$this->error('不能锁定自己！');
		}
		$Model			 = D("AdminUser");
		$rs				 = $Model->field('status')->find($id);
		$status			 = $rs['status'] ? 0 : 1;
		$data['status']	 = $status;
		$info			 = $status ? '解锁' : '锁定';
		if ( false !== $Model->where("id='$id'")->save($data) ) {
			$this->success($info . '成功!');
		} else {
			$this->error($info . '失败!');
		}
	}

	/**
	 * 修改密码
	 */
	public function EditPass() {
		$this->addStep('修改密码');
		if ( $this->isPost() ) {
			$OldPass = $_POST['old_password'];
			$NewPass = $_POST['new_password'];
			$rpass = $_POST['confirm_password'];
			if($NewPass != $rpass){
				$this->error("两次输入的密码不同");
			}
			
			$adminUser = D('AdminUser')->find($this->_admin_user_id);
			if(encrypt_pwd($OldPass) != $adminUser['password']){
				$this->error("旧密码不正确");
			}
			D('AdminUser')->where("id='{$this->_admin_user_id}'")->save(array(
				'password' => encrypt_pwd($NewPass)
			));
			if(D('AdminUser')->getDbError()){
				$this->error("修改密码失败".D('AdminUser')->getDbError());
			}
			$this->success("修改密码成功");
		}
		$this->display('EditPass');
	}

}

?>