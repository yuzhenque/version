<?php

/**
 * 首页
 *
 * @author bluefoot<bluefoot@qq.com>
 * @version 2014-06-03
 */
class IndexAction extends CommonAction
{
	/**
	 * 显示主界面
	 */
	public function index()
	{
		@header("Location:" . U('ErpVersion/index'));
		
		$this->display();
	}
	
	/**
	 * 图标
	 */
	public function icon()
	{
		$this->display();
	}
	
	public function zhuaqu()
	{
		$url = array(
			'http://www.qichacha.com/search_index?key=%25E6%2597%2585%25E8%25A1%258C%25E7%25A4%25BE&ajaxflag=1&index=2&tel=T&province=AH&p=1',
			'http://www.qichacha.com/search_index?key=%25E6%2597%2585%25E8%25A1%258C%25E7%25A4%25BE&ajaxflag=1&index=2&tel=T&province=AH&p=2',
			'http://www.qichacha.com/search_index?key=%25E6%2597%2585%25E8%25A1%258C%25E7%25A4%25BE&ajaxflag=1&index=2&tel=T&province=AH&p=3',
			'http://www.qichacha.com/search_index?key=%25E6%2597%2585%25E8%25A1%258C%25E7%25A4%25BE&ajaxflag=1&index=2&tel=T&province=AH&p=4',
		);
		
		include_once(ROOT . 'Public/Class/Net/SlHttp.class.php');
		
		SlHttp::getInstance()->setHeader(array(
											 'DNT'              => 1,
											 'Host'             => 'www.qichacha.com',
											 'Referer'          => 'http://www.qichacha.com/search_index?key=%25E6%2597%2585%25E8%25A1%258C%25E7%25A4%25BE',
											 'X-Requested-With' => 'XMLHttpRequest',
											 'Cookie'           => 'acw_tc=AQAAACLP6UGu1QAAAsaaPeVUsmDzF5EJ; PHPSESSID=uqvvmk95ird77sac7a4a7dsve6; gr_user_id=9be6f7a5-a154-4379-b06a-9c21edbcb146; UM_distinctid=15bd66e094117a-041918925e9c8f8-183d292f-1fa400-15bd66e0942241; CNZZDATA1254842228=556687871-1493946663-%7C1494049302; _uab_collina=149395118769115408644203; _umdata=0712F33290AB8A6D3ADDAA4365033809C835D6ABB683827D0900B01F214773032BC1DA094F80EFDECD43AD3E795C914CDC8DB1AA5F1528F4A9757CD55BB1D4AD; gr_session_id_9c1eb7420511f8b2=cee8e4b6-7c59-4b12-aa08-9144a9e5b941',
										 ));
		$res = SlHttp::getInstance()->sendRequest($url[0], array(), 'get');
//		dump($res);
		
		$start = strpos($res, '<tbody>');
		$end   = strpos($res, '</tbody>');
		
		$string = substr($res, $start + 7, $end - $start - 7);

//		eregi("<tbody>(.*)<\/tbody>", $res, $b);
		dump($start);
		dump($end);
		dump($string);
		exit;
	}
	
	/**
	 * 初始化数据
	 */
	public function init_data()
	{
		$path = ROOT . 'bdata/a/';
		
		$files = scandir($path);
		foreach ($files as $v) {
			if ($v == '.' || $v == '..') {
				continue;
			}
			
			$file = $path . $v;
			
			$data = simplexml_load_file($file);
			dump($data);
			exit;
		}
		
		dump($files);
		exit;
		
	}
	
}

?>