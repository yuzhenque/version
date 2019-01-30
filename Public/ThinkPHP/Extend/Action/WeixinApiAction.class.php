<?php

/**
 * ------------------------------------
 * WxApi微信调用管理
 *
 * @author Bluefoot. 2013-12-24
 * ------------------------------------
 */
class WeixinApiAction extends Action
{
	
	/**
	 * TOKEN
	 */
	protected $_token = '';
	
	
	/**
	 * 微信公用类初始化
	 */
	protected $_wxCls = null;
	
	/**
	 * 析构函数
	 * 判断是否有权限
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->_token	= Conf('WX_TOKEN', 'weixin');
		
		require_once ROOT .'Public/Class/Weixin/Weixin.class.php';
		$this->_wxCls		 = new Weixin(Conf('WX_TOKEN', 'weixin'), Conf('WX_APPID', 'weixin'), Conf('WX_APPSECERT', 'weixin'));
	}
	
	public function index()
	{
		//接口配置响应
		$this->_wxCls->response();
		
		$message = $this->_wxCls->getMessage();
		//模拟数据
//		$message = array(
//			'type' => 'event',
//			'event' => 'subscribe',
//			'from'	=> 't',
//			'Ticket' => 't',
//			'text' => '测试'
//		);
		
		if ($message !== false) {
			
			//记录消息
			if($message['type'] != 'event') {
				D('MessageWeixin')->add(array(
					'type'				 => $this->_type,
					'from_openid'		 => trim($message['from']),
					'msgtype'			 => trim($message['type']),
					'content'			 => !empty($message['text']) ? trim($message['text']) : ($message['type'] == 'event' ? json_encode($message) : ''),
					'msgid'				 => $message['id'],
					'picurl'			 => !empty($message['image']) ? trim($message['image']) : '',
					'location_x'		 => !empty($message['x']) ? trim($message['x']) : '',
					'location_y'		 => !empty($message['y']) ? trim($message['y']) : '',
					'location_scale'	 => !empty($message['scale']) ? trim($message['scale']) : '',
					'location_label'	 => !empty($message['label']) ? trim($message['label']) : '',
					'link_title'		 => !empty($message['title']) ? trim($message['title']) : '',
					'link_description'	 => !empty($message['des']) ? trim($message['des']) : '',
					'link_url'			 => !empty($message['url']) ? trim($message['url']) : '',
					'create_time'		 => trim($message['time'])
				));
			}
			switch ($message['type']) {
				//文本消息
				case 'text' :
					//关键字进入应用
					$data	 = $this->_getApp($message['from'], $message['text']);
					//进入普通关键字获取
					!empty($data) or $data = $this->_getContent($message['from'], $message['text']);
					//无关键字内容则获取自动响应内容
					!empty($data) or $data = $this->_replyNoKeyword($message['from']);
					break;
				//图片消息
				case 'image' :
					
					break;
				//事件消息
				case 'event' :
					//如果是二维码扫描
					if(!empty($message['ticket'])){
						$data = $this->_ticket($message['from'], $message['ticket']);
					}
					//首次关注回复
					elseif($message['event'] == 'subscribe') {
						$data	 = $this->_subscribe($message['from']);
					}
					//取消关注事件处理
					elseif($message['event'] == 'unsubscribe'){
						
					}
					//点击事件
					elseif($message['event'] == 'click') {
						//获取响应内容
						$data	 = $this->_menuClick($message['from'], $message['key']);
					}
					break;
				//链接消息
				case 'link' :
					
					break;
				//地理位置消息
				case 'location' :
					
					break;
			}
			if ($data) {
				$msgtype = is_array($data) ? 'news' : 'text';
				$this->_wxCls->replyMessage($message['to'], $message['from'], $data, $msgtype);
			}elseif($message['type'] == 'text'){
				$this->_wxCls->replyMessage($message['to'], $message['from'], "未知消息", 'text');
			}
		}
	}
	
	/**
	 * 扫描商家二维码
	 */
	protected function _ticket($p_openId, $p_ticket)
	{
		return '';
	}
	
	/**
	 * 首次关注回复
	 * @param type $p_openId
	 */
	protected function _subscribe($p_openId)
	{
		//回复内容
		$data = D('ReplyFocusOn')->where("type='{$this->_type}'")->find();
		if($data['msgtype'] == 'text'){
			return $data['content'];
		}
		$datas = D('ReplyFocusOnNews')->where("parent_id='{$this->_type}'")->order("is_cover DESC")->select();
		foreach ($datas as $key=>$vo) {
			$message[]	 = array(
				'title'	 => $vo['title'],
				'desc'	 => '',
				'image'	 => $vo['image'] ? IMG_URL . $vo['image'] : '',
				'url'	 => !empty($vo['link']) ? $vo['link'] .(strpos($vo['link'], '?') !== false ? ':' : '?').'openid='. $p_openId : U('WxSource/replyfocuson', 'id='.$vo['id'], true, false, true)
			);
		}
		return $message;
	}
	
	/**
	 * 菜单点击消息处理
	 * 
	 * @param string $p_openId		用户OPENID
	 * @param string $p_event		事件内容
	 */
	private function _menuClick($p_openId, $p_event)
	{
		//菜单事件处理
		if(strpos($p_event, 'menu_id_') == 0){
			$menuId = str_replace('menu_id_', '', $p_event);
			$menu = D('WxMenu')->where("id='{$menuId}'")->find();
			if(empty($menu)){
				return "无此菜单{$p_event}信息";
			}
			//如果是应用
			if($menu['btntype'] == 'app'){
				return $this->_parseAppUrl($p_openId, $menu);
			}
			//如果是文本消息
			elseif($menu['msgtype'] == 'text'){
				return $menu['content'];
			}
			//如果是图文消息
			elseif($menu['msgtype'] == 'news'){
				$datas = D('MenuNews')->where("parent_id='{$menuId}'")->order("is_cover DESC")->select();
				if(!empty($datas)){
					foreach ($datas as $vo) {
						$message[]	 = array(
							'title'	 => $vo['title'],
							'desc'	 => '',
							'image'	 => IMG_URL . $vo['image'],
							'url'	 => !empty($vo['link']) ? $vo['link'] .(strpos($vo['link'], '?') !== false ? ':' : '?').'openid='. $p_openId : U('WxSource/menu', 'id='.$vo['id'], true, false, true)
						);
					}
				}else{
					$message = '未找到内容';
				}
				return $message;
			}
			
			return $menu['name'];
		}else{
			return '未知事件';
		}
	}
	
	/**
	 * 关键字获取消息
	 */
	private function _getContent($p_openId, $keyword)
	{
		$keyword	 = trim($keyword);
		if (empty($keyword)){
			return null;
		}
		$model		 = D('ReplyKeyword');
		$condition	 = "keyword LIKE '%#{$keyword}#%'";
		$count		 = $model->where($condition)->count();
		if($count == 0){
			return null;
		}
		if($count == 1){
			$data = $model->where($condition)->find();
			if($data['msgtype'] == 'text'){
				return $data['content'];
			}
		}
		if ($count > 1) {
			$datas = $model->where($condition)->limit(0, C('REPLY_KEYWORD_SHOW_MAX'))->select();
			$message = array();
			foreach ($datas as $vo) {
				$message[]	 = array(
					'title'	 => $vo['title'],
					'desc'	 => '',
					'image'	 => IMG_URL . $vo['image'],
					'url'	 => !empty($vo['link']) ? $vo['link'] .(strpos($vo['link'], '?') !== false ? ':' : '?').'openid='. $p_openId : U('WxSource/replyKeyword', 'id='.$vo['id'], true, false, true)
				);
			}
			//添加查看更多链接
			if ($count > 1) {
				$message[] = array(
					'title'	 => '查看更多 >>',
					'desc'	 => '',
					'image'	 => '',
					'url'	 => U('WxSource/replyKeywordList', 'keyword=' . $keyword, true, false, true)
				);
			}
			unset($datas);
			return $message;
		}

		return null;
	}
	
	
	/**
	 * 未搜索到关键字回复
	 * @param type $p_openId
	 */
	private function _replyNoKeyword($p_openId)
	{
		$data = D('ReplyNoKeyword')->find();
		if($data['msgtype'] == 'text'){
			return $data['content'];
		}
		$datas = D('ReplyNoKeywordNews')->order("is_cover DESC")->select();
		foreach ($datas as $vo) {
			$image = $vo['image'] ? IMG_URL . $vo['image'] : '';
			$message[]	 = array(
				'title'	 => $vo['title'],
				'desc'	 => '',
				'image'	 => $image,
				'url'	 => !empty($vo['link']) ? $vo['link'] .(strpos($vo['link'], '?') !== false ? ':' : '?').'openid='. $p_openId : U('WxSource/replyNoKeyword', 'id='.$vo['id'], true, false, true)
			);
		}
		return $message;
	}
	
	/**
	 * 进入应用
	 * @param type $p_openId		用户的OPENID
	 * @param type $p_str			用户发送过来的查询内容
	 */
	private function _getApp($p_openId, $p_str)
	{
		$app = D('Menu')->where("type='{$this->_type}' AND app_keyword LIKE '%#{$p_str}#%'")->find();
		if(!empty($app)){
			return $this->_parseAppUrl($p_openId, $app);
		}
		return null;
	}
	
	/**
	 * 解析菜单内地址
	 */
	private function _parseAppUrl($p_openId, $p_app){
		if(empty($p_app['link'])){
			$p_app['link'] = 'Index/index';
		}
		if(strpos($p_app['link'], 'http://') !== 0){
			$arr = explode("|", $p_app['link']);
			$p_app['link'] = U($arr[0], (isset($arr[1]) ? $arr[1].'&openid='. $p_openId : 'openid='. $p_openId), true, false, true);
		}
		$restr = $p_app['app_content']. "\n" ."<a href='". $p_app['link'] ."'>点击进入{$p_app['name']}</a>";
		return $restr;
	}
}
?>