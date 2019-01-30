<?php

/**
 * ------------------------------------
 * 区域插件
 *
 * @author Bluefoot. 2014-03-15
 * ------------------------------------
 */
class AreaWidget extends CommonAction
{
	
	/**
	 * 用于显示选择框
	 * @param type $p_provinceId	省ID
	 * @param type $p_cityId		市ID
	 * @param type $p_arId	县ID
	 */
	public function index($p_pre='', $p_provinceId, $p_cityId, $p_arId, $p_require=1)
	{
		
		//提取全部省
		$allProvinceArr = D('Area')->where("ar_type=1 AND ar_status=1")->select();
		//提取全部市
		$allCityArr = D('Area')->where("ar_type=2 AND ar_status=1")->select();
		//提取全部区
		$allPrefectureArr = D('Area')->where("ar_type=3 AND ar_status=1")->select();
		
		$this->assign('allProvinceArr',		$allProvinceArr);
		$this->assign('allCityArr',			$allCityArr);
		$this->assign('allPrefectureArr',	$allPrefectureArr);
		$this->assign('provinceId',			$p_provinceId);
		$this->assign('cityId',				$p_cityId);
		$this->assign('arId',				$p_arId);
		$this->assign('pre',				$p_pre);
		$this->assign('require',			$p_require);
		$this->display("./Lib/Widget/Tpl/Area/index.html");
	}

	/**
	 * 用于显示选择框
	 * @param type $p_provinceId	省ID
	 * @param type $p_cityId		市ID
	 * @param type $p_arId	县ID
	 * @param type $p_communityId	商圏
	 */
	public function community($p_pre='', $p_provinceId, $p_cityId, $p_arId, $p_communityId, $p_require=1)
	{
		
		//提取全部省
		$allProvinceArr = D('Area')->where("ar_type=1 AND ar_status=1")->select();
		//提取全部市
		$allCityArr = D('Area')->where("ar_type=2 AND ar_status=1")->select();
		//提取全部区
		$allPrefectureArr = D('Area')->where("ar_type=3 AND ar_status=1")->select();
		//提取所有商圏
		$allCommunityArr = D('Community')->where("community_status=1")->select();
		
		$this->assign('allProvinceArr',		$allProvinceArr);
		$this->assign('allCityArr',			$allCityArr);
		$this->assign('allPrefectureArr',	$allPrefectureArr);
		$this->assign('provinceId',			$p_provinceId);
		$this->assign('cityId',				$p_cityId);
		$this->assign('arId',				$p_arId);
		$this->assign('communityId',		$p_communityId);
		$this->assign('allCommunityArr',	$allCommunityArr);
		$this->assign('pre',				$p_pre);
		$this->assign('require',			$p_require);
		$this->display("./Lib/Widget/Tpl/Area/community.html");
	}
}

?>