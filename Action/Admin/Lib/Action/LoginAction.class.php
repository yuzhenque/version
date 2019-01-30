<?php
/**
 * 登录管理
 * 
 * @author bluefoot<bluefoot@qq.com>
 * @version 2014-06-04
 */
class LoginAction extends Action {
	private $SendMsg = array(
		1=>'随机短信验证码已成功发送到您的手机上，请留意查收！',
		2=>'对不起，本系统只支持厦门移动手机号！',
		3=>'发送失败，请联系管理员！',
		4=>'对不起，手机号不能为空！',
		5=>'对不起，此手机已超过系统设定发送次数！',
	);
    public function index(){
    	if($this->isPost()){
    		$username = $this->_POST('username');
    		$password = $this->_POST('password');
    		$Model = D("AdminUser");
    		$reid = $Model->login($username,$password);
    		if($reid==1){
    			$this->success('登录成功！', U('Index/index'));
    		}
    		elseif($reid==2){
    			$this->error('对不起，您输入的用户名或密码有误！');
    		}
    		elseif($reid==3){
    			$this->error('对不起，用户名和密码不能为空！');
    		}else{
    			$this->error('操作异常！');
    		}
    	}
        $this->display();
    }
	public function Logout(){
		session(null);
		$this->success('退出系统成功！', U('Login/index'));
	}
}
?>