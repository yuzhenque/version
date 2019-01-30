<?php
/**
 * 支付宝手机支付
 * 
 * @Write By Bluefoot
 */
class Alipay extends Pay {
	/**
	 * 前台回调
	 * 
	 * @return boolean
	 */
	public function respone() {
		$alipay_config = $this->config();
		
		include_once 'alipay/alipay_notify.class.php';
		
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();
		//验证成功
		if ($verify_result){
			if ('success' == strtolower($_GET['result'])){
				
				//商户订单号
				$this->_params['order_trade_no'] = $_REQUEST['out_trade_no'];
				//支付宝交易号
				$this->_params['out_trade_no']	 = $_REQUEST['trade_no'];
				//买家账号
				$this->_params['buyer_account']	 = $_REQUEST['buyer_email'];
				//金额
				$this->_params['amount']		 = $_REQUEST['total_fee'];
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 后台回调
	 * 
	 * @return boolean
	 */
	public function notice() {
		$alipay_config = $this->config();
		include_once 'alipay/alipay_notify.class.php';
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();

		
		if ($verify_result){
			$notify_data = $_POST['notify_data'];
			
			$notify = simplexml_load_string($notify_data);
			
			if($notify->trade_status == 'TRADE_SUCCESS' || $notify->trade_status == 'TRADE_FINISHED'){
				$this->_success = true;
				//商户订单号
				$this->_params['order_trade_no'] = $notify->out_trade_no;
				//支付宝交易号
				$this->_params['out_trade_no']	 = $notify->trade_no;
				//买家账号
				$this->_params['buyer_account']	 = $notify->buyer_email;
				//金额
				$this->_params['amount']		 = $notify->total_fee;

				return true;
			}
		}
		return false;
	}
	
	/**
	 * 创建跳转回调
	 * 
	 * @param type $p_data
	 * @return type
	 */
	public function buildData($p_data) {
		include_once 'alipay/alipay_submit.class.php';
		$alipay_config = $this->config();
		
		
		//返回格（必填，不需要修改）
		$format = "xml";
		
		//返回格式（必填，不需要修改）
		$v = "2.0";
		
		//请求号 必填，须保证每次请求都是唯一
		$req_id = date('Ymdhis') . uniqid();
		$extra_common_param = authcode(session('user_id') . '|'. time(), false);
		$notify_url = U('Order/notify', 'order_id='. $p_data['order_id'] .'&ext='.urlencode($extra_common_param), true, false, true);
		$return_url = U('Order/respone', 'order_id='. $p_data['order_id'] .'&ext='.urlencode($extra_common_param), true, false, true);
		
		$merchant_url   = $_SERVER['HTTP_HOST'];
		
		//请求业务参数详细（必填）
		$req_data = '<direct_trade_create_req>'.
						'<notify_url>' . $notify_url . '</notify_url>'.
						'<call_back_url>' . $return_url . '</call_back_url>'.
						'<seller_account_name>' . $alipay_config['seller_email'] . '</seller_account_name>'.
						'<out_trade_no>' . $p_data['order_trade_no'] . '</out_trade_no>'.
						'<subject>' . $p_data['order_name'] . '</subject>'.
						'<total_fee>' . round($this->_testpay ? 0.01 : $p_data['order_total_price'],2) . '</total_fee>'.
						'<merchant_url>' . $merchant_url . '</merchant_url>'.
					'</direct_trade_create_req>';
		
		//构造要请求的参数数组，无需改动
		$para_token = array(
			"service"		 => "alipay.wap.trade.create.direct",
			"partner"		 => trim($alipay_config['partner']),
			"sec_id"		 => trim($alipay_config['sign_type']),
			"format"		 => $format,
			"v"				 => $v,
			"req_id"		 => $req_id,
			"req_data"		 => $req_data,
			"_input_charset" => trim(strtolower($alipay_config['input_charset']))
		);

		//建立请求
		$alipaySubmit = new AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestHttp($para_token);
		
		//解析远程模拟提交后返回的信息
		$para_html_text = $alipaySubmit->parseResponse(urldecode($html_text));
		
		//获取request_token
		$request_token = $para_html_text['request_token'];
		
		/**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/
		
		//业务详细(必填)
		$req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
		
		//构造要请求的参数数组，无需改动
		$parameter = array(
			"service"		 => "alipay.wap.auth.authAndExecute",
			"partner"		 => trim($alipay_config['partner']),
			"sec_id"		 => trim($alipay_config['sign_type']),
			"format"		 => $format,
			"v"				 => $v,
			"req_id"		 => $req_id,
			"req_data"		 => $req_data,
			"_input_charset" => trim(strtolower($alipay_config['input_charset']))
		);

		//建立请求
		$html = $alipaySubmit->buildRequestForm($parameter, 'get');
		
		return $html;
	}
	
	/**
	 * 获取配置
	 * @return array
	 */
	public function config(){
		
		
		if(!empty($this->_companyId)){
			$companyConfig = Conf('', 'alipay', $this->_companyId);
			$config = Conf('', 'alipay');
			$config['STATUS'] = $companyConfig['STATUS'];
			$config['ACCOUNT'] = $companyConfig['ACCOUNT'];
		}else{
			$config = Conf('', 'alipay');
		}
		
		if($config['STATUS'] != 1){
			return false;
		}
		
		if($config['TEST'] == 1){
			$this->_testpay = true;
		}else{
			$this->_testpay = false;
		}
		
		//支付宝账号
		$alipay_config['seller_email']		= trim($config['ACCOUNT']);
		
		//合作身份者id，以2088开头的16位纯数字
		$alipay_config['partner']		= trim($config['MID']);
	
		//安全检验码，以数字和字母组成的32位字符
		$alipay_config['key']			= trim($config['KEY']);
	
		//签名方式 不需修改
		$alipay_config['sign_type']    = 'MD5';
	
		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset']= 'utf-8';
	
		//ca证书路径地址，用于curl中ssl校验
		$alipay_config['cacert']    = dirname(__FILE__).'\\alipay\\cacert.pem';
	
		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipay_config['transport']    = 'http';
	
		return $alipay_config;
	}
}

?>