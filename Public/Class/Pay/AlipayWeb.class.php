<?php
/**
 * 支付宝PC支付
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
		
		include_once 'alipay/web_alipay_notify.class.php';
		$alipayNotify = new AlipayNotify($alipay_config);

		$verify_result = $alipayNotify->verifyReturn();
		
		if ($verify_result){

			$trade_no		= $_REQUEST['out_trade_no'];	// 商户订单号
			$out_trade_no 	= $_REQUEST['trade_no'];		// 支付宝交易号
			$trade_status 	= $_REQUEST['trade_status'];	// 交易状态
			$buyer_email	= $_REQUEST['buyer_email']; 	// 支付宝买家帐号
			$result			= $_REQUEST['result'];			// 支付结果
			$total_fee		= $_REQUEST['total_fee'];		// 收到金额

			if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS' || $result == 'success') {
				
				//商户订单号
				$this->_params['order_trade_no'] = $trade_no;
				//支付宝交易号
				$this->_params['out_trade_no']	 = $out_trade_no;
				//买家账号
				$this->_params['buyer_account']	 = $buyer_email;
				//金额
				$this->_params['amount']		 = $total_fee;

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
		
		include_once 'alipay/web_alipay_notify.class.php';
		$alipayNotify = new AlipayNotify($alipay_config);

		$verify_result = $alipayNotify->verifyNotify();
		
		if ($verify_result){

			$trade_no		= $_REQUEST['out_trade_no'];	// 商户订单号
			$out_trade_no 	= $_REQUEST['trade_no'];		// 支付宝交易号
			$trade_status 	= $_REQUEST['trade_status'];	// 交易状态
			$buyer_email	= $_REQUEST['buyer_email']; 	// 支付宝买家帐号
			$result			= $_REQUEST['result'];			// 支付结果
			$total_fee		= $_REQUEST['total_fee'];		// 收到金额

			if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS' || $result == 'success') {

				//商户订单号
				$this->_params['order_trade_no'] = $trade_no;
				//支付宝交易号
				$this->_params['out_trade_no']	 = $out_trade_no;
				//买家账号
				$this->_params['buyer_account']	 = $buyer_email;
				//金额
				$this->_params['amount']		 = $total_fee;

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
		
		//初始参数
		$extra_common_param = authcode(session('user_id') . '|'. time(), false);
		$notify_url = U('Order/notify', 'order_id='. $p_data['order_id'] .'&ext='.urlencode($extra_common_param), true, false, true);
		$return_url = U('Order/respone', 'order_id='. $p_data['order_id'] .'&ext='.urlencode($extra_common_param), true, false, true);
		
		
		//支付类型默认为1
		$payment_type = "1";

		$subject 			= $p_data['order_name'];
		$total_fee 			= $this->_testpay == true ? 0.01 : $p_data['order_total_price'];
		$body 				= '';				// 订单描述
		$show_url 			= '';				// 商品展示地址，需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html
		$anti_phishing_key 	= "";				// 防钓鱼时间戳，若要使用请调用类文件submit中的query_timestamp函数
		$exter_invoke_ip 	= "";				// 客户端的IP地址，非局域网的外网IP地址，如：221.0.0.1
		

		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" 		=> "create_direct_pay_by_user",
				"partner" 		=> trim($alipay_config['partner']),
				"payment_type"	=> $payment_type,
				"notify_url"	=> $notify_url,
				"return_url"	=> $return_url,
				"seller_email"	=> $alipay_config['seller_email'],
				"out_trade_no"	=> $p_data['order_trade_no'],
				"subject"		=> $subject,
				"total_fee"		=> $total_fee,
				"body"			=> $body,
				"show_url"		=> $show_url,
				"extra_common_param" => '',
				"anti_phishing_key"	=> $anti_phishing_key,
				"exter_invoke_ip"	=> $exter_invoke_ip,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);

		//建立请求
		$alipaySubmit = new AlipaySubmit($alipay_config);
		
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");

		return $html_text;
	}
	
	/**
	 * 获取配置
	 * 
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