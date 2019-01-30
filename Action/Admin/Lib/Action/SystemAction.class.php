<?php
/**
 * 
 * +------------------------------------------------------------+
 * @category systemAction 
 * +------------------------------------------------------------+
 * 系统设置功能模块
 * +------------------------------------------------------------+
 *
 * @author anzai <sba3198178@126.com>
 * @copyright http://www.suncco.com 2014
 * @version 1.0
 *
 * Modified at : 2014-2-25 09:33:28
 *
 */
class SystemAction extends CommonAction{
	/**
	 * 系统设置
	 */
	public function index(){
		
		require(ROOT . "/Conf/Admin/setting.cfg.php");
		
		$type = 'base';
		$Model = D("System");
		
		if($this->isPost()){
			
			$status = $Model->submitdata(0, $type);
			
			delDir(ROOT . 'SiteData/Runtime/Admin/Data/');
			$this->success('修改成功！');
		}
		$data = $Model->getValue(0, $type);
		$this->assign('data', $data);
		$this->assign('list', $g_settingCfg[$type]);
		$this->assign('title', '系统参数设置');
		$this->display();
	}

	public function weixin()
	{
		require(ROOT . "/Conf/Admin/setting.cfg.php");
		
		$type = 'weixin';
		$Model = D("System");
		
		if($this->isPost()){
			$status = $Model->submitdata(0, $type);
			delDir(ROOT . 'SiteData/Runtime/Admin/Data/');
			$this->success('修改成功！');
		}
		$data = $Model->getValue(0, $type);
		$this->assign('data', $data);
		$this->assign('list', $g_settingCfg[$type]);
		$this->display('index');
	}

	public function wxpay()
	{
		require(ROOT . "/Conf/Admin/setting.cfg.php");
		
		$type = 'wxpay';
		$Model = D("System");
		
		if($this->isPost()){
			$status = $Model->submitdata(0, $type);
			delDir(ROOT . 'SiteData/Runtime/Admin/Data/');
			$this->success('修改成功！');
		}
		$data = $Model->getValue(0, $type);
		$this->assign('data', $data);
		$this->assign('list', $g_settingCfg[$type]);
		$this->display('index');
	}
	
	/**
	 * 系统设置
	 */
	public function sms(){
		require(ROOT . "/Conf/Admin/setting.cfg.php");
		
		$type = 'sms';
		$Model = D("System");
		
		if($this->isPost()){
			
			$status = $Model->submitdata(0, $type);
			
			delDir(ROOT . 'SiteData/Runtime/Admin/Data/');
			$this->success('修改成功！');
		}
		$data = $Model->getValue(0, $type);
		$this->assign('data', $data);
		$this->assign('list', $g_settingCfg[$type]);
		$this->assign('title', '短信设置');
		$this->display('index');
	}
	
	/**
	 * 系统设置
	 */
	public function system(){
		require(ROOT . "/Conf/Admin/setting.cfg.php");
		
		$type = 'system';
		$Model = D("System");
		
		if($this->isPost()){
			
			$status = $Model->submitdata(0, $type);
			
			delDir(ROOT . 'SiteData/Runtime/Admin/Data/');
			$this->success('修改成功！');
		}
		$data = $Model->getValue(0, $type);
		$this->assign('data', $data);
		$this->assign('list', $g_settingCfg[$type]);
		$this->assign('title', '参数设置');
		$this->display('index');
	}
	
	
	/**
	 * MMC缓存刷新 
	 * 显示表管理界面
	 */
	public function memcache()
	{
		$mmcTbCfg = C('MMC_TB_CFG');
		ksort($mmcTbCfg);
		$cache = Cache :: getInstance('Memcache');
//		dump($cache->mGetStats());
		$this->assign('connected', $cache->connected);
		$this->assign('mmcTbCfg', $mmcTbCfg);
		$this->display();
	}
	
	
	/**
	 * 刷新表缓存 
	 */
	public function flushTable()
	{
		$tables = $_POST['tables'];
		if(empty($tables)){
			$this->assign('waitSecond',1000);
			$this->error('没有选中表，请重新选择！', '__URL__/memcache');
		}
		$Model = D("AdminSystem");
		foreach($tables as $key=>$val){
			echo $Model->flushOneTable($val);
		}
	}
	
	/**
	 * 更新MMC里的所有缓存
	 */
	public function flushAllTable()
	{
		$cache = Cache :: getInstance('Memcache');
		$cache->clear();
		
		$this->success('删除所有MMC缓存成功！', '__URL__/memcache');
	}
	
	/**
	 * 删除文件缓存 
	 */
	public function flushFileCache()
	{
		delDir(ROOT . 'SiteData/Runtime/');
		
		$this->success('删除文件缓存成功！');
	}
		
}
?>