<?php

/**
 * 
 * 微信支付
 * 
 * @Write By Bluefoot
 */
class Wxpay extends Pay {

	/**
	 * 前台回调
	 * 
	 * @return boolean
	 */
	public function respone() {
		$this->config();

		$this->_params['order_trade_no'] = substr($_GET['out_trade_no'], 0, strlen($_GET['out_trade_no'])-10); //订单号
		$this->_params['out_trade_no']	 = $_GET['trade_no'];  //交易号
		return true;
	}

	/**
	 * 后台回调
	 * 
	 * @return boolean
	 */
	public function notice() {
		$this->config();

		include_once dirname(__FILE__) . '/wxpay/lib/WxPay.Api.php';
		include_once dirname(__FILE__) . '/wxpay/lib/WxPay.Notify.php';


		$notify	 = new WxPayNotify();
		$result	 = $notify->Handle(false);


		$xml	 = $GLOBALS['HTTP_RAW_POST_DATA'];
		$data	 = WxPayResults::Init($xml);
		
//		@file_put_contents(ROOT.'SiteData/Logs/'. time() .'_2.txt', $xml);

		
		if ($result != false) {

			//获取通知的数据
			$xml	 = $GLOBALS['HTTP_RAW_POST_DATA'];
			$data	 = WxPayResults::Init($xml);

			$this->_success				 = true;
			$this->_params['order_trade_no']	 = substr($data['out_trade_no'], 0, strlen($data['out_trade_no'])-10);  //订单号
			$this->_params['out_trade_no']		 = $data['transaction_id']; //交易号
			//买家账号
			$this->_params['buyer_account']	 = $data['openid'];
			//金额
			$this->_params['amount']		 = $data['total_fee'];
			return true;
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
		
		$this->config();
		
		include_once dirname(__FILE__) . '/wxpay/lib/WxPay.Api.php';
		include_once dirname(__FILE__) . '/wxpay/WxPay.JsApiPay.php';

		//①、获取用户openid
		$tools = new JsApiPay(); 
		if (empty($p_data['order_openid'])) {
//			$openId = $tools->GetOpenid(isset($p_data['url']) && $p_data['url'] ? $p_data['url'] : HTTP_URL);
			$openId = $tools->GetOpenid();
		}
		else {
			$openId = $p_data['order_openid'];
		}
		
		//②、统一下单
		$uid = session('user_id');
		if(empty($uid)){
			$usercode = cookie('usercode');
			list($uid) = explode('\t', getAuthCode($usercode));
		}
		$extra_common_param	 = authcode($uid . '\t' .$p_data['order_id'] .'\t'. time(), false);
		
		$notify_url = 'http://'.$_SERVER['HTTP_HOST'].'/Payment/wxpay.php';
		
		$price = $p_data['order_total_price'] - $p_data['order_card_reduce'] - $p_data['order_coupon_reduce'];
		
		$input	 = new WxPayUnifiedOrder();
		$input->SetBody('微信支付订单');
		$input->SetAttach($extra_common_param);
		$input->SetOut_trade_no($p_data['order_trade_no'].time());
		$input->SetTotal_fee(round($this->_testpay ? 0.01 : $price, 2) * 100);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag($p_data['order_name']);
		$input->SetNotify_url($notify_url);
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order	 = WxPayApi::unifiedOrder($input);
		
		if($order['return_code'] == 'FAIL'){
			return false;
		}
		return $tools->GetJsApiParameters($order);
	}
	
	/**
	 * 预支付,用于扫码付
	 * 
	 * @param type $p_data
	 */
	public function unifiedorder($p_data)
	{
		$this->config();
		
		include_once dirname(__FILE__) . '/wxpay/lib/WxPay.Api.php';
		include_once dirname(__FILE__) . '/wxpay/WxPay.JsApiPay.php';
		
		$uid = session('user_id');
		if(empty($uid)){
			$usercode = cookie('usercode');
			list($uid) = explode('\t', getAuthCode($usercode));
		}
		$extra_common_param	 = authcode($uid . '\t' .$p_data['order_id'] .'\t'. time(), false);
		
		$notify_url = 'http://'.$_SERVER['HTTP_HOST'].'/Payment/wxpay.php';
		
		$input	 = new WxPayUnifiedOrder();
		$input->SetBody('微信支付订单');
		$input->SetAttach($extra_common_param);
		$input->SetOut_trade_no($p_data['order_trade_no'].time());
		$input->SetTotal_fee(round($this->_testpay ? 0.01 : $p_data['order_total_price'], 2) * 100);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag($p_data['order_name']);
		$input->SetNotify_url($notify_url);
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id($p_data['order_trade_no']);
		
		$order	 = WxPayApi::unifiedOrder($input);
		if(!empty($order)){
			return $order['code_url'];
		}else{
			return false;
		}
	}

	/**
	 * 获取配置
	 * 
	 * @return array
	 */
	public function config() {

		$config = Conf('', 'wxpay');
		
		$this->_testpay = false;

		//设置常量
		if (!defined('WXPAY_APPID')) {
			define('WXPAY_APPID', $config['WXPAY_APPID']);
			define('WXPAY_APPSECRET', $config['WXPAY_APPSECRET']);
			define('WXPAY_PAYKEY', $config['WXPAY_PAYKEY']);
			define('WXPAY_MCHID', $config['WXPAY_MCHID']);
			define('WXPAY_CURL_TIMEOUT', 30);
		}
	}

}

?>