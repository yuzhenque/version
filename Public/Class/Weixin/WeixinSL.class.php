<?php
/**
 * 
 * +------------------------------------------------------------+
 * @category MpWeixinSL 
 * +------------------------------------------------------------+
 * 公众微信平台模拟类
 * +------------------------------------------------------------+
 *
 * @author Bluefoot
 * @copyright http://www.suncco.com 2013
 * @version 1.0
 *
 * Modified at : 2013-10-28
 */
class WeixinSL {
	/**
	 * 数据接收及发送类
	 */
	private $_http = '';
	
	/**
	 * 重试次数
	 */
	private $_tryCount = 0;
	
	/**
	 * 登陆错误时记录错误代码
	 */
	private $_loginErr = 0;
	
	/**
	 * 用户名
	 */
	private $_user = '';
	
	/**
	 * 密码
	 */
	private $_pwd = '';
	
	/**
	 * 模拟登陆使用的 Cookie 保存的文件
	 */
	private $_cookiePath = '';
	
	/**
	 * 模拟登陆保存的有效期，默认为10分钟，即隔10分钟强制重新登陆一次
	 */
	private $_expire = 20;
	
	/**
	 * 模拟登陆使用的 token 保存的文件
	 */
	private $_tokenPath = '';
	
	/**
	 * 模拟登陆使用的URL前置
	 */
	private $_url = 'https://mp.weixin.qq.com/';
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name __construct
	 * +------------------------------------------------------------+
	 * 构造函数
	 * +------------------------------------------------------------+
	 *
	 * @param string $p_user	用户名
	 * @param string $p_pwd		密码
	 *
	 */
	public function __construct($p_user, $p_pwd)
	{
		require_once ROOT .'Public/Class/Net/SlHttp.class.php';
		$this->_http		 = SlHttp::getInstance();
		$this->_user		 = $p_user;
		$this->_pwd			 = $p_pwd;
		$this->_cookiePath	 = WEIXIN_TEMP .'/'. md5($p_user) .'/cookie.log';
		$this->_tokenPath	 = WEIXIN_TEMP .'/'. md5($p_user) .'/token.log';
		@mkdirs(WEIXIN_TEMP .'/'. md5($p_user));
		@mkdirs(WEIXIN_TEMP .'/'. md5($p_user));
	}
	
	/**
	 * 检查用户名和密码是否正确
	 * 
	 * @param string $p_user	用户名
	 * @param string $p_pwd		密码
	 * @return boolean 正确或不正确
	 */
	public function testAccount($p_user, $p_pwd)
	{
		$this->_user		 = $p_user;
		$this->_pwd			 = $p_pwd;
		return $this->login(false);
	}
	
	/**
	 * 获取TOKEN信息
	 */
	public function token()
	{
		return file_get_contents($this->_tokenPath);
	}
	
	/**
	 * 模拟登陆接口
	 * 
	 * @param string $p_user	用户名
	 * @param string $p_pwd		用户密码（需要MD5）
	 * 
	 * @return boolean 是否登陆成功标识
	 */
	public function login($p_cache=true)
	{
		//检查是否已有登陆过且未过有效期
		if($p_cache && is_file($this->_cookiePath) && filemtime($this->_cookiePath) > time() - $this->_expire * 60){
			return true;
		}
		
		$this->_tryCount++;
		
		$post = array(
			'username'	 => $this->_user,
			"pwd"		 => $this->_pwd,
			"imgcode"	 => "",
			"f"			 => "json",
		);
		
		$this->_http->setHeader(array(
			'Referer' => $this->_url
		));
        $this->_http->sendRequest($this->_url .'cgi-bin/login?lang=zh_CN', $post);
        $result = json_decode($this->_http->getResultData(), TRUE);
		$this->_loginErr = $result['base_resp']['ErrCode'];
		if((int)$result['base_resp']['ErrCode'] == -6 && $this->_tryCount <= 50){
			$this->login();
			return false;
		}
		if((int)$result['base_resp']['ErrCode'] != 0) {
			return false;
		}
		
		$this->_loginErr = 0;
		
		//如果要缓存的话，写入文件内缓存起来
		if($p_cache){
			$cookie = '';
			if (preg_match_all("/set\-cookie: (.*) path/i", $this->_http->getResultHeader(), $matches)) {
				if (isset ($matches[1])) {
					foreach ($matches[1] as $v) {
						$cookie .= $v;
					}
				}
			}
			file_write($this->_cookiePath, $cookie);
			//获取 token 并写入缓存文件
			preg_match("/token=(\d+)/is", $result['redirect_url'], $match);
			file_write($this->_tokenPath, $match[1]);
		}
		
		return true;
	}
	
	/**
	 * 获取当前登陆的错误信息
	 */
	public function loginErr()
	{
		return $this->_loginErr;
	}
	
	/**
	 * 获取账号信息
	 * 
	 */
	public function getAccountInfo()
	{
		if(!$this->login()){
			return array(
				'status' => 0
			);
		}
		$url = 'cgi-bin/advanced?action=dev&t=advanced/dev&token='. file_get_contents($this->_tokenPath) . '&lang=zh_CN&f=json';
		
		$this->_http->setHeader(array(
			'Referer' => $this->_url .'cgi-bin/advanced?action=dev&t=advanced/dev&token='. file_get_contents($this->_tokenPath) .'&lang=zh_CN',
			'Cookie' => file_get_contents($this->_cookiePath)
		));
        $this->_http->sendRequest($this->_url . $url, array(), 'get');
        $result = $this->_http->getResultData();
		var_dump($result);exit();
		$result = json_decode($result, true);
		
		return array_merge(array(
			'status' => 1
		), $result);
	}
	
	/**
	 * 关闭编辑模式
	 */
	public function closeEditMod()
	{
		return $this->_advancedSwitchForm(1, 0);
	}
	
	/**
	 * 开启开发模式
	 */
	public function openDevelopMod()
	{
		return $this->_advancedSwitchForm(2, 1);
	}
	
	/**
	 * 设置URL及TOKEN，用于接收及反馈消息等
	 * 
	 * @param string $p_url		URL
	 * @param string $p_token	TOKEN
	 */
	public function setProfile($p_url, $p_token)
	{
		if(!$this->login()){
			return array(
				'status' => 0
			);
		}
		
		$url = 'cgi-bin/callbackprofile?t=ajax-response&token='. file_get_contents($this->_tokenPath) .'&lang=zh_CN';
		
		$this->_http->setHeader(array(
			'Referer' => $this->_url .'cgi-bin/advanced?action=interface&t=advanced/interface&token='. file_get_contents($this->_tokenPath) .'&lang=zh_CN',
			'Cookie' => file_get_contents($this->_cookiePath)
		));
		$token = rand_string(10);
		$post = array(
			'callback_token' => $p_token,
			'url'			 => $p_url
		);
		
        $this->_http->sendRequest($this->_url . $url, $post, 'post');
        $result = $this->_http->getResultData();
		
		$result = json_decode($result, true);
		if($result['ret'] == 0){
			return array(
				'status' => 1
			);
		}else{
			return array(
				'status' => 0
			);
		}
	}
	
	
	/**
	 * 获取用户分组数据
	 */
	public function getGroupList()
	{
		if(!$this->login()){
			return array(
				'status' => 0
			);
		}
		$url = 'cgi-bin/contactmanage?t=user/index&token=' .
				file_get_contents($this->_tokenPath) . '&lang=zh_CN&pagesize='. $p_pageSize .'&pageidx='. $p_page .'&groupid=0';
		
		$this->_http->setHeader(array(
			'Referer' => $this->_url .'cgi-bin/message?t=message/list&count=20&day=7&token='. file_get_contents($this->_tokenPath) .'&lang=zh_CN',
			'Cookie' => file_get_contents($this->_cookiePath)
		));
        $this->_http->sendRequest($this->_url . $url, array(), 'get');
        $result = $this->_http->getResultData();
		
		$groupList = array();
		
		if(preg_match('/groupsList : \((.*?)\).groups/i', $result, $match)) {
			$groupList = json_decode($match[1], true);
		}
		return array(
			'status' => 1,
			'groupList' => $groupList['groups'],
		);
	}
	
	/**
	 * 编辑用户组
	 * 
	 * @param string	 $p_method		操作方式['add','rename','del']
	 * @param int	 $p_groupId		用户组ID
	 * @param string $p_groupName		用户组名
	 */
	public function modifyGroup($p_method, $p_groupId, $p_groupName)
	{
		if(!$this->login()){
			return array(
				'status' => 0
			);
		}
		$url = 'cgi-bin/modifygroup';;
		$post	 = array(
			't'		 => 'ajax-friend-group',
			'ajax'	 => 1,
			'f'		 => 'json',
			'lang'	 => 'zh_CN',
			'func'	 => $p_method,
			'id'	 => $p_groupId,
			'name'	 => $p_groupName,
			'random' => rand(100000, 9000000),
			'token'	 => file_get_contents($this->_tokenPath)
		);
		$this->_http->setHeader(array(
			'Referer' => $this->_url .'cgi-bin/contactmanage?t=user/index&pagesize=10&pageidx=0&type=0&token='. file_get_contents($this->_tokenPath) .'&lang=zh_CN',
			'Cookie' => file_get_contents($this->_cookiePath)
		));
        $this->_http->sendRequest($this->_url . $url, $post, 'post');
        $result = json_decode($this->_http->getResultData(), true);
		
		if($result['ErrCode'] == '0'){
			return array(
				'status' => 1,
				'group_id' => $result['GroupId'],
			);
		}else{
			return array(
				'status' => 0
			);
		}
	}
	
	/**
	 * 获取所有用户，分页形式
	 * 
	 * @param int $p_groupId	用户组ID
	 * @param int $p_page		当前页
	 * @param int $p_pageSize	每页提取几条
	 * @return array 提取到的用户数据
	 */
	public function getFriendList($p_groupId, $p_page, $p_pageSize = 50)
	{
		if(!$this->login()){
			return array(
				'status' => 0
			);
		}
		$url = 'cgi-bin/contactmanage?t=user/index&token=' .
				file_get_contents($this->_tokenPath) . '&lang=zh_CN&pagesize='. $p_pageSize .'&pageidx='. $p_page .'&type=0&groupid='.$p_groupId;
		
		$this->_http->setHeader(array(
			'Referer' => $this->_url .'cgi-bin/message?t=message/list&count=20&day=7&token='. file_get_contents($this->_tokenPath) .'&lang=zh_CN',
			'Cookie' => file_get_contents($this->_cookiePath)
		));
        $this->_http->sendRequest($this->_url . $url, array(), 'get');
        $result = $this->_http->getResultData();
		
		$friendList = array();
		
		if(preg_match('/friendsList : \((.*?)\).contacts/i', $result, $match)) {
			$friendList = json_decode($match[1], true);
		}
		$pageCount = 0;
		if(preg_match('#pageCount : (.*?),#i', $result, $allpage)){
			$pageCount = (int)$allpage[1];
		}
		return array(
			'status' => 1,
			'pageCount'	 => $pageCount,
			'friendList' => $friendList['contacts'],
		);
	}
	
	/**
	 * 获取指定用户的头像
	 * 
	 * @param int $p_userId 用户ID
	 */
	public function getFriendHeaderImg($p_userId)
	{
		if(!$this->login()){
			return array(
				'status' => 0
			);
		}
		$url = "cgi-bin/getheadimg?fakeid={$p_userId}&token=". file_get_contents($this->_tokenPath) ."&lang=zh_CN";
		
		$this->_http->setHeader(array(
			'Referer' => $this->_url ."cgi-bin/singlemsgpage?fromfakeid=" . $p_userId . "&msgid=&source=&count=20&t=wxm-singlechat&lang=zh_CN",
			'Cookie' => file_get_contents($this->_cookiePath)
		));
        $this->_http->sendRequest($this->_url . $url, array(), 'get');
		
		return array(
			'status' => 1,
			'imginfo' => $this->_http->getResultData()
		);
	}
	/**
	 * 获取某个用户的详细信息
	 * 
	 * @param int $p_userId 用户ID
	 */
	public function getFriendContactInfo($p_userId)
	{
		if(!$this->login()){
			return array(
				'status' => 0
			);
		}
		$url = "cgi-bin/getcontactinfo";
		$post	 = array(
			't'		 => 'ajax-getcontactinfo',
			'ajax'	 => 1,
			'f'		 => 'json',
			'lang'	 => 'zh_CN',
			'fakeid' => $p_userId,
			'token'	 => file_get_contents($this->_tokenPath)
		);
		$this->_http->setHeader(array(
			'Referer' => $this->_url ."cgi-bin/singlemsgpage?fromfakeid=" . $p_userId . "&msgid=&source=&count=20&t=wxm-singlechat&lang=zh_CN",
			'Cookie' => file_get_contents($this->_cookiePath)
		));
        $this->_http->sendRequest($this->_url . $url, $post, 'post');
		$result = $this->_http->getResultData();
		
		$result = json_decode($result, true);
		return array(
			'status' => 1,
			'info'	 => $result['contact_info']
		);
	}
	
	/**
	 * 更新用户的用户组信息
	 * 
	 * @param int		$p_uid			用户ID
	 * @param int		$p_groupId		用户组ID
	 * @return type
	 */
	public function modifyUserGroup($p_uid, $p_groupId)
	{
		if(!$this->login()){
			return array(
				'status' => 0
			);
		}
		$url = 'cgi-bin/modifycontacts';;
		$post	 = array(
			'action'	 => 'modifycontacts',
			't'		 => 'ajax-putinto-group',
			'ajax'	 => 1,
			'f'		 => 'json',
			'lang'	 => 'zh_CN',
			'tofakeidlist'	 => $p_uid,
			'contacttype' => $p_groupId,
			'random' => rand(100000, 9000000),
			'token'	 => file_get_contents($this->_tokenPath)
		);
		$this->_http->setHeader(array(
			'Referer' => $this->_url .'cgi-bin/contactmanage?t=user/index&pagesize=10&pageidx=0&type=0&token='. file_get_contents($this->_tokenPath) .'&lang=zh_CN',
			'Cookie' => file_get_contents($this->_cookiePath)
		));
        $this->_http->sendRequest($this->_url . $url, $post, 'post');
        $result = json_decode($this->_http->getResultData(), true);
		if(isset($result['result']) && isset($result['result'][0]) && $result['result'][0]['ret'] == '0'){
			return array(
				'status' => 1
			);
		}else{
			return array(
				'status' => 0
			);
		}
	}
	
	/**
	 * 更新用户备注
	 * 
	 * @param int		$p_uid			用户ID
	 * @param array		$p_remark		备注
	 * @return type
	 */
	public function modifyUserRemark($p_uid, $p_remark)
	{
		if(!$this->login()){
			return array(
				'status' => 0
			);
		}
		$url = 'cgi-bin/modifycontacts';;
		$post = array(
			'action'	 => 'setremark',
			't'			 => 'ajax-response',
			'ajax'		 => 1,
			'f'			 => 'json',
			'lang'		 => 'zh_CN',
			'tofakeuin'	 => $p_uid,
			'remark'	 => $p_remark,
			'random'	 => rand(100000, 9000000),
			'token'		 => file_get_contents($this->_tokenPath)
		);
		$this->_http->setHeader(array(
			'Referer' => $this->_url .'cgi-bin/contactmanage?t=user/index&pagesize=10&pageidx=0&type=0&token='. file_get_contents($this->_tokenPath) .'&lang=zh_CN',
			'Cookie' => file_get_contents($this->_cookiePath)
		));
        $this->_http->sendRequest($this->_url . $url, $post, 'post');
        $result = json_decode($this->_http->getResultData(), true);
		if($result['ret'] == '0'){
			return array(
				'status' => 1
			);
		}else{
			return array(
				'status' => 0
			);
		}
	}
	
	/**
	 * 发送信息给指定人
	 * 
	 * @param  $p_tofakeid	发送ID
	 * @param  $p_content	发送内容
	 */
	public function singleSend($p_tofakeid, $p_content){
		if(!$this->login()){
			return array(
				'status' => 0
			);
		}
		$post = array();
		$post['tofakeid'] = $p_tofakeid;
		$post['type'] 	  = 1;
		$post['content']  = $p_content;
		$post['ajax'] 	  = 1;
		$post['quickreplyid'] = '';
		$post['token'] = file_get_contents($this->_tokenPath);
		$post['mask'] = false;
        $post['error'] = 'false';
		
		$this->_http->setHeader(array(
			'Referer' => $this->_url ."cgi-bin/message?t=message/list&token={$post['token']}&count=20&day=7&lang=zh_CN",
			'Cookie' => file_get_contents($this->_cookiePath)
		));
        $this->_http->sendRequest($this->_url .'cgi-bin/singlesend?t=ajax-response&lang=zh_CN', $post);
        $result = json_decode($this->_http->getResultData(), TRUE);
		if($result['msg'] == 'ok'){
			return array(
				'status' => 1,
				'info'	 => 'ok'
			);
		}else{
			return array(
				'status' => 0,
				'info'	 => $result['msg']
			);
		}
	}
	
	public function test()
	{
		$post = array();
		
		$this->_http->setHeader(array(
		));
		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxbc9360797be908b8&secret=17e644380dad94c269c4c84c60d1f1ae';
        
		$this->_http->sendRequest($url, $post);
        $result = json_decode($this->_http->getResultData(), TRUE);
		var_dump($result);
	}
	
	/**
	 * 开启或关闭模式
	 * 
	 * @param int $p_type	[1编辑模式2开发模式]
	 * @param int $p_flag	[0关闭1开启]
	 * @return type
	 */
	private function _advancedSwitchForm($p_type, $p_flag)
	{
		if(!$this->login()){
			return array(
				'status' => 0
			);
		}
		$url = 'cgi-bin/skeyform?form=advancedswitchform&lang=zh_CN';
		
		$this->_http->setHeader(array(
			'Referer' => $this->_url,
			'Cookie' => file_get_contents($this->_cookiePath)
		));
		$post = array(
			'flag' => $p_flag,
			'type' => $p_type,
			'token' => file_get_contents($this->_tokenPath)
		);
		
        $this->_http->sendRequest($this->_url . $url, $post, 'post');
        $result = $this->_http->getResultData();
		
		$result = json_decode($result, true);
		if($result["base_resp"]['ret'] == 0){
			return array(
				'status' => 1
			);
		}else{
			return array(
				'status' => 0
			);
		}
	}
}
?>