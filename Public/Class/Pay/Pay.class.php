<?php
/**
 * 支付类
 * 
 * @Write By Bluefoot
 */
class Pay{
	/**
	 * 支付方式相关配置
	 */
	protected $_config;
	
	/**
	 * 支付成功后返回的参数
	 */
	protected $_params;
	
	/**
	 * 支付是否成功
	 */
	protected $_success = false;
	
	/**
	 * 是否测试模式,此时每次仅支付0.01元
	 */
	protected $_testpay = false;
	
	/**
	 * 支付方式标识
	 */
	protected $_payType;
	
	/**
	 * 公司ID
	 */
	protected $_companyId;
	
	public function __construct($p_payType, $p_companyId = 0)
	{
		$this->_payType		 = $p_payType;
		$this->_companyId	 = $p_companyId;
	}

	/**
	 * 构建支付表单
	 * @param array $p_data 包括
	 * 		order_name	: 订单名称
	 * 		order_sn	: 订单编号（唯一）,
	 * 		total		: 订单价格
	 */
	public function buildData($p_data){
		
	}
	
	/**
	 * 同步请求
	 */
	public function respone(){
		
	}
	
	/**
	 * 异步请求
	 */
	public function notice(){
		
	}
	
	/**
	 * 返回结果
	 */
	public function params(){
		return $this->_params;
	}
	
	/**
	 * 获取支付状态
	 * 
	 * @param type $exit
	 * @return boolean|string
	 */
	public function success($exit = true){
		//异步
		if ($this->_success){
			if ($exit) {
				exit('success');
			}else{
				return 'ok';
			}
		}else{
			return true;
		}
	}
	
	/**
	 * 获取配置信息
	 * 
	 * @param string $identy
	 * @return multitype:|Ambigous <NULL, multitype:>
	 */
	public function getConfig($identy = null){
		if (null === $identy){
			return $this->_config;
		}else{
			return isset($this->_config[$identy]) ? $this->_config[$identy] : null;
		}
	}
	
	/**
	 * 实例化支付方式类
	 * 
	 * @param int $p_payType	支付方式
	 * @param int $p_companyId	商家ID,如果商家ID有自己的支付,则使用自己的支付,否则不使用
	 */
	public static function factory($p_payType, $p_companyId=0){
		
		$payPath = ROOT . '/Public/Class/Pay/';
		switch ($p_payType){
			//支付宝
			case 3:
				if(TEMPLATE == 'web'){
					include_once $payPath . 'AlipayWeb.class.php';
				}else{
					include_once $payPath . 'AlipayWap.class.php';
				}
				
				$payment = new alipay($p_payType, $p_companyId);
				break;
			//银联
			case 4:
				include_once $payPath . 'unionpay.class.php';
				$payment = new Unionpay($p_payType, $p_companyId);
				break;
			//微信支付
			case 5:
				include_once $payPath . 'Wxpay.class.php';
				$payment = new Wxpay($p_payType, $p_companyId);
				break;
			default:
				return false;
		}
		
		return $payment;
	}
}

?>