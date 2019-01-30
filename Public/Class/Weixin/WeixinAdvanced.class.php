<?php
/**
 * 
 * +------------------------------------------------------------+
 * @category WeixinAdvanced 
 * +------------------------------------------------------------+
 * 获取用户信息、用户组新、发送信息等MpWeixin扩展类库
 * (针对认证通过的公众微信服务号)
 * 高级接口
 * +------------------------------------------------------------+
 *
 * @author anzai <sba3198178@126.com>
 * @copyright http://www.suncco.com 2013
 * @version 1.0
 *
 * Modified at : 2013-10-31 13:57:19
 *
 */
class WeixinAdvanced extends MpWeixin {
	
	///获取用户信息的接口URL
	private static $_get_userinfo_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s';

	private static $_group_url = array(
		'get'	=> 'https://api.weixin.qq.com/cgi-bin/groups/get?access_token=',	//获取分组信息URL
		'create'=> 'https://api.weixin.qq.com/cgi-bin/groups/create?access_token=',	//创建分组信息URL
		'update'=> 'https://api.weixin.qq.com/cgi-bin/groups/update?access_token=',	//修改分组信息URL
		'move'	=> 'https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=',	//移动分组URL
	);
	
	///发送客服消息URL
	private static $_send_url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=';
	
	///获取关注者列表URL
	private static $_getuserlist_url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=';
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name getUserInfo
	 * +------------------------------------------------------------+
	 * 获取关注者基本信息
	 * +------------------------------------------------------------+
	 *
	 * @param string $openid 普通用户的标识
	 * @return 返回用户信息，包含字段信息有：
	 * ┌────────┬─────────────────────────────┐
	 * │字段		│描述					      │
	 * ├────────┼─────────────────────────────┤
	 * │openid	│用户的标识					  │
	 * ├────────┼─────────────────────────────┤
	 * │sex     │性别，1男 2女 0未知			  │
	 * ├────────┼─────────────────────────────┤
	 * │city    │所在城市					  │
	 * ├────────┼─────────────────────────────┤
	 * │lang    │用户的语言，简体中文为zh_CN	  │
	 * ├────────┼─────────────────────────────┤
	 * │sex     │性别，1男 2女 0未知			  │
	 * ├────────┼─────────────────────────────┤
	 * │head    │用户头像					  │
	 * ├────────┼─────────────────────────────┤
	 * │time    │用户关注时间，为时间戳		  │
	 * └────────┴─────────────────────────────┘
	 *
	 */
	public function getUserInfo($openid){
		$json = $this->_get(sprintf(self::$_get_userinfo_url, $this->_getToken(), $openid));
		if (isset($json->subscribe) && empty($json->errmsg)){
			//用户没有关注该公众号，拉取不到其余信息
			if ($json->subscribe == 0){
				return null;
			}else{
				return array(
					'openid'	=> $json->openid,			//用户的标识
					'nickname'	=>	$json->nickname,		//用户的昵称
					'sex'		=> 	$json->sex,				//用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
					'city'		=> 	$json->city,			//用户所在城市
					'lang'		=>	$json->language,		//用户的语言，简体中文为zh_CN
					'head'		=>	$json->headimgurl,		//用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像）
					'time'		=>	$json->subscribe_time	//用户关注时间，为时间戳
				);
			}
		}else{
			$this->_errorno = $json->errcode;
			return false;
		}
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name getGroup
	 * +------------------------------------------------------------+
	 * 获取用户分组信息
	 * +------------------------------------------------------------+
	 *
	 * @return 访问一个二维数组，每条分组包含字段信息有：
	 * ┌──────┬─────────────────────────────┐
	 * │字段      │描述							│
	 * ├──────┼─────────────────────────────┤
	 * │id	  │分组id，由微信分配			│
	 * ├──────┼─────────────────────────────┤
	 * │name  │分组名字，UTF8编码			│
	 * ├──────┼─────────────────────────────┤
	 * │count │分组内用户数量				│
	 * └──────┴─────────────────────────────┘
	 *
	 */
	public function getGroup(){
		$json = $this->_get(self::$_get_group_url . $this->_getToken());
		if (isset($json['groups'])) {
			return $json['groups'];
		}else{
			$this->_errorno = $json->errcode;
			return false;
		}
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name createGroup
	 * +------------------------------------------------------------+
	 * 创建分组
	 * +------------------------------------------------------------+
	 *
	 * @param string/array $name 数组名称（30个字符以内）
	 * @return 创建成功返回true
	 *
	 */
	public function createGroup($name){
		$name = trim($name);
		if ($name == ''){
			$this->_errorno = 'MP001';
			return false;
		}
		$data = array2Json(array('group' => array('name' => $name)));
		$json = $this->_post(self::$_group_url['create']. $this->_getToken(), $data);
		if ((int)$json->errcode == self::SUCCESS_CODE) {
			$group = $json->group;
			return array(
				'id' => $group['id'],
				'name' => $group['name']
			);
		}else{
			$this->_errorno = $json->errcode;
			return false;
		}
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name updateGroup
	 * +------------------------------------------------------------+
	 * 修改分组信息
	 * +------------------------------------------------------------+
	 *
	 * @param int 	 $id   分组id，由微信分配
	 * @param string $name 分组名字（30个字符以内）
	 *
	 */
	public function updateGroup($id, $name){
		$data = array2Json(array(
			'id' => $id,
			'name' => $name
		));
		
		$json = $this->_post(self::$_group_url['update'] . $this->_getToken(), $data);
		
		if (self::SUCCESS_CODE == $json->errcode) {
			return true;
		}else{
			$this->_errorno = $json->errcode;
			return false;
		}
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name moveUser
	 * +------------------------------------------------------------+
	 * 移动用户分组
	 * +------------------------------------------------------------+
	 * 
	 * @example moveUser('oDF3iYx0ro3_7jD4HFRDfrjdCM58',108)
	 *
	 * @param string $openid	用户唯一标识符
	 * @param int    $group_id	分组id
	 * @return	成功返回true
	 *
	 */
	public function moveUser($openid, $group_id){
		$moveData = array(
			'openid' => $openid,
			'to_groupid' => $group_id
		);
		
		$json = $this->_post(self::$_group_url['move'] . $this->_getToken(), array2Json($moveData));
		
		if (self::SUCCESS_CODE == $json->errcode) {
			return true;
		}else{
			$this->_errorno = $json->errcode;
			return false;
		}
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name getUserList
	 * +------------------------------------------------------------+
	 * 获取关注者列表
	 * +------------------------------------------------------------+
	 *
	 * @param string $openid 第一个拉取的OPENID，不填默认从头开始拉取
	 *
	 */
	public function getUserList($openid = ''){
		$openid = $openid ? '&next_openid=' . $openid : '';
		$json = $this->_get(self::$_getuserlist_url . $this->_getToken() . $openid);
		if ((int) $json->errcode == self::SUCCESS_CODE){
			return $json;
		}else{
			$this->_errorno = $json->errcode;
			return false;
		}
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name _sendMessage
	 * +------------------------------------------------------------+
	 * 发送客服消息
	 * +------------------------------------------------------------+
	 *
	 * @param string 		$touser		普通用户openid
	 * @param array/string  $data		消息
	 * @param unknown_type  $msgType	消息类型
	 *
	 */
	private function _sendMessage($touser, $data, $msgType = self::SEND_MSGTYPE_TEXT){
		$data = array(
			'touser'  => $touser,
			'msgtype' => $msgType,
			$msgType  => $data
		);
		
		$json = $this->_post(self::$_send_url . $this->_getToken(), array2Json($data));
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name sendText
	 * +------------------------------------------------------------+
	 * 发送客服文本消息
	 * +------------------------------------------------------------+
	 *
	 * @param string $touser	普通用户openid
	 * @param string $text		文本消息
	 *
	 */
	public function sendText($touser, $text){
		$data = array(
			'content' => $text
		);
		
		$this->_sendMessage($touser, $data, self::SEND_MSGTYPE_TEXT);
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name sendImage
	 * +------------------------------------------------------------+
	 * 发送客服图片消息
	 * +------------------------------------------------------------+
	 *
	 * @param string $touser	普通用户openid
	 * @param int 	 $image_id	发送的图片的媒体ID
	 *
	 */
	public function sendImage($touser, $image_id){
		$data = array(
			'media_id' => $image_id
		);
		
		$this->_sendMessage($touser, $data, self::SEND_MSGTYPE_IMAGE);
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name sendVoice
	 * +------------------------------------------------------------+
	 * 发送客服语音消息
	 * +------------------------------------------------------------+
	 *
	 * @param string $touser	普通用户openid
	 * @param int	 $voice_id	发送的语音的媒体ID
	 *
	 */
	public function sendVoice($touser, $voice_id){
		$data = array(
			'media_id' => $voice_id
		);
		
		$this->_sendMessage($touser, $data, self::SEND_MSGTYPE_VOICE);
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name sendVideo
	 * +------------------------------------------------------------+
	 * 发送客服视频消息
	 * +------------------------------------------------------------+
	 *
	 * @param string $touser	普通用户openid
	 * @param int 	 $video_id	发送的视频的媒体ID
	 * @param int 	 $thumb_id	视频缩略图的媒体ID
	 *
	 */
	public function sendVideo($touser, $video_id, $thumb_id){
		$data = array(
			'media_id'		 => $video_id,
			'thumb_media_id' => $thumb_id
		);
		
		$this->_sendMessage($touser, $data, self::SEND_MSGTYPE_VIDEO);
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name sendMusic
	 * +------------------------------------------------------------+
	 * 发送客服音乐消息
	 * +------------------------------------------------------------+
	 *	
	 * @param string $touser		普通用户openid
	 * @param string $musicurl		音乐链接
	 * @param string $hqmusicurl	高品质音乐链接，wifi环境优先使用该链接播放音乐
	 * @param int	 $thumb_id		视频缩略图的媒体ID
	 * @param string $title			音乐标题
	 * @param string $description	音乐描述
	 *
	 */
	public function sendMusic($touser, $musicurl, $hqmusicurl, $thumb_id, $title='', $description){
		$data = array(
			'title'			=> $title,
			'description'	=> $description,
			'musicurl'		=> $musicurl,
			'hqmusicurl'	=> $hqmusicurl,
			'thumb_media_id'=> $thumb_id
		);
		
		$this->_sendMessage($touser, $data, self::SEND_MSGTYPE_MUSIC);
	}
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name sendNews
	 * +------------------------------------------------------------+
	 * 发送客服图文客服消息
	 * +------------------------------------------------------------+
	 *
	 * @param string $touser	普通用户openid
	 * @param array  $articles	图文消息(图文消息条数限制在10条以内)
	 * 
	 * 一个二维数组，每个元素包含：
	 * ┌────────────┬─────────────────────────────────────────┐
	 * │字段      	    │描述						  			  │
	 * ├────────────┼─────────────────────────────────────────┤
	 * │title	    │标题					      			  │
	 * ├────────────┼─────────────────────────────────────────┤
	 * │description │描述					      			  │
	 * ├────────────┼─────────────────────────────────────────┤
	 * │url         │点击后跳转的链接			  			  │
	 * ├────────────┼─────────────────────────────────────────┤
	 * │picurl	    │图文消息的图片链接(支持JPG、PNG格式，较好	  │
	 * │		    │的效果为大图640*320，小图80*80)	  		  │
	 * └────────────┴─────────────────────────────────────────┘
	 *
	 */
	public function sendNews($touser, $articles){
		$data = array(
			'articles' => count($articles) > 10 ? array_slice($articles, 0, 10) : $articles
		);
		
		$this->_sendMessage($touser, $data, self::SEND_MSGTYPE_NEWS);
	}
}