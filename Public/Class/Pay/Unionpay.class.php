<?php
/**
 * 
 * +------------------------------------------------------------+
 * @category unionpay
 * +------------------------------------------------------------+
 * 银联手机支付
 * +------------------------------------------------------------+
 *
 *
 */
class Unionpay extends Pay {
	protected function respone() {
		$unionpay_config = $this->config();
		include_once 'unionpay/func/secureUtil.php';
		
		if (isset ( $_POST ['signature'] )) {
			$result = verify ( $_POST );
			if($result){
				$this->_params['order_trade_no'] = $_POST['orderId'];	//订单号
				$this->_params['out_trade_no'] = $_POST['queryId'];		//交易号
				return true;
			}
		} else {
			return false;
		}
	}
	
	protected function notice() {
		$unionpay_config = $this->config();
		
		include_once 'unionpay/func/secureUtil.php';
		
		
		
		if (isset ( $_POST ['signature'] )) {
			$result = verify ( $_POST );
			if($result){
				$this->_success = true;
				$this->_params['order_trade_no'] = $_POST['orderId'];	//订单号
				$this->_params['out_trade_no'] = $_POST['queryId'];		//交易号
				return true;
			}
		} else {
			return false;
		}
	}
	
	public function buildData($userdata) {
		$unionpay_config = $this->config();
		
		include_once 'unionpay/func/secureUtil.php';
		
		
		//请求号 必填，须保证每次请求都是唯一
		$notice_extra_common_param = authcode($this->_member_id.',notify,'.time(), false);
		$return_extra_common_param = authcode($this->_member_id.',return,'.time(), false);
		$notify_url = HTTP_HOST . 'pay/unionpay.php?extra_common_param=' . $notice_extra_common_param;
		$return_url = HTTP_HOST . 'pay/unionpay.php?extra_common_param=' . $return_extra_common_param;
		
		//**req_data详细信息**
		$params = array(
			'version'		 => '5.0.0', //版本号
			'encoding'		 => 'utf-8', //编码方式
			'certId'		 => getSignCertId(), //证书ID
			'txnType'		 => '01', //交易类型	
			'txnSubType'	 => '01', //交易子类
			'bizType'		 => '000201', //业务类型
			'frontUrl'		 => $return_url, //前台通知地址
			'backUrl'		 => $notify_url, //后台通知地址	
			'signMethod'	 => '01', //签名方法
			'channelType'	 => '08', //渠道类型，07-PC，08-手机
			'accessType'	 => '0', //接入类型
			'merId'			 => $unionpay_config['account'], //商户代码，请改自己的测试商户号
			'orderId'		 => $userdata['order_sn'], //商户订单号
			'txnTime'		 => date('YmdHis'), //订单发送时间
			'txnAmt'		 => $userdata['total'] * 100, //交易金额，单位分
			'currencyCode'	 => '156', //交易币种
			'defaultPayType' => '0001', //默认支付方式	
//			'orderDesc'		 => $userdata['order_name'], //订单描述，网关支付和wap支付暂时不起作用
			'reqReserved'	 => $this->_member_id, //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现
		);

		// 签名
		sign($params);

		// 构造 自动提交的表单
		$html_text	 = create_html($params, $unionpay_config['front_url']);
		
		return $html_text;
	}
	
	/**
	 * 获取配置
	 * 
	 * @return array
	 */
	public function config(){
		
		if(!empty($this->_companyId)){
			$companyConfig = Conf('', 'unionpay', $this->_companyId);
			$config = Conf('', 'unionpay');
			$config['STATUS'] = $companyConfig['STATUS'];
			$config['ACCOUNT'] = $companyConfig['ACCOUNT'];
		}else{
			$config = Conf('', 'unionpay');
		}
		
		if($config['STATUS'] != 1){
			return false;
		}
		
		if($config['TEST'] == 1){
			$this->_testpay = true;
		}else{
			$this->_testpay = false;
		}
		
		//合作身份者id，以2088开头的16位纯数字
		$unionpay_config['account'] = trim($config['MID']);

		//证书
		$unionpay_config['cert']			 = trim($config['cert']);
		//证书密码
		$unionpay_config['cert_password']	 = trim($config['KEY']);
		
		$path = $this->_testpay == true ? 'test' : 'product';
		
		define('SDK_SIGN_CERT_PWD',		$unionpay_config['cert_password']);
		define('SDK_SIGN_CERT_PATH',	dirname(__FILE__). '/unionpay/'. $path .'/'. $unionpay_config['cert']);
		define('SDK_ENCRYPT_CERT_PATH', dirname(__FILE__). '/unionpay/'. $path .'/verify_sign_acp.pfx');
		define('SDK_VERIFY_CERT_DIR',	dirname(__FILE__). '/unionpay/'. $path .'/');
		define('SDK_FILE_DOWN_PATH',	ROOT_PATH.'temp/log/unionpay/');
		
		//支付路径
		if($unionpay_config['sandbox'] == 1){
			$unionpay_config['front_url'] = 'https://101.231.204.80:5000/gateway/api/frontTransReq.do';
		}else{
			$unionpay_config['front_url'] = 'https://gateway.95516.com/gateway/api/frontTransReq.do';
		}
		
		return $unionpay_config;
	}
}

?>