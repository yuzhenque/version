<?php
class AdWidget extends CommonAction{
	public function index($position, $ad_key, $number=1, $p_tpl='index', $p_other=''){

		$Model = D('ad');
		
		$condition['ad_key'] = $ad_key;
		$condition['province_id'] = array(
			'IN', array(0, $this->_province_id)
		);
		$condition['city_id'] = array(
			'IN', array(0, $this->_city_id)
		);
		$time = time();
		$condition['_string'] = "(ad_start_time<='{$time}' OR ad_start_time=0) AND (ad_end_time>='{$time}' OR ad_end_time=0)";
		
		$ad_list	= $Model->where($condition)->order("ad_order_id ASC, ad_id DESC")->limit(0, $number)->select();
		if(!empty($ad_list)){
			foreach($ad_list as $k=>$v){
				if(!empty($v['ad_goods_id'])){
					$ad_list[$k]['goods'] = D('Goods')->find($v['ad_goods_id']);
				}
			}
		}
		
		$this->assign('position',	$position);
		$this->assign('ad_list',	$ad_list);
		$this->assign('other',	$p_other);
		$this->display("./Lib/Widget/Tpl/Ad/{$p_tpl}.html");
	}
}


?>