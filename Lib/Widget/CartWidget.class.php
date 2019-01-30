<?php

/**
 * 购物车相关挂件
 * 
 * @Write By Bluefoot. 2015-08-30
 */
class CartWidget extends CommonAction
{

	/**
	 * 生成价格重新选择
	 * 
	 * @param type $p_gotyId		商品类型ID
	 * @param type $p_useDate		起始时间
	 * @param type $p_endDate		结束时间
	 * @param type $p_effMin			最小时间 
	 * @param type $p_effMax		最大时间
	 */
	public function date_step($p_gotyId, $p_useDate, $p_endDate = 0, $p_effMin = 0, $p_effMax = 0, $isPc = ture)
	{
		$is_end	 = !empty($p_endDate) ? true : false;
//		$is_end	 = true;


		$date_format = array(
			'minDate'			 => $p_effMin ? date('Y-m-d', $p_effMin) : date('Y-m-d', time()),
			'maxDate'			 => $p_effMax ? date('Y-m-d', $p_effMax) : '',
			'onpicked'			 => 'function(){g_cartCls.reload_price('.$p_gotyId.');}'
		);

		$p_useDate =  $p_useDate ? $p_useDate : time();
		if($p_useDate < strtotime($date_format['minDate'])){
			$p_useDate = strtotime($date_format['minDate']);
		}
		$this->assign('gotyId', $p_gotyId);
		$this->assign('is_end', $is_end);
		$this->assign('useDate', date('Y-m-d',$p_useDate));
		$this->assign('endDate', date('Y-m-d', $p_endDate ? $p_endDate : time() + 86400));
		$this->assign('isPc', $isPc);
		$this->assign('date_format', $date_format);
		$this->display("./Lib/Widget/Tpl/Cart/date_step.html");
	}

	/**
	 * 数量选择器
	 * 
	 * @param type $p_gotyId
	 * @param type $p_defaultValue
	 * @param type $p_stock
	 */
	public function number($p_gotyId, $p_defaultValue)
	{
		$this->assign('gotyId', $p_gotyId);
		$this->assign('defaultValue', $p_defaultValue);
		$this->display("./Lib/Widget/Tpl/Cart/number.html");
	}

}

?>