<?php

/*
 * Created on 2011-4-8
 *
 * Author: hwc123 <hwc123@139.com>
 *
 * Copyright (c) 2011 http://www.suncco.com All rights reserved.
 */

/**
  +----------------------------------------------------------------------------
 * 偏业务方面的 通用函数
  +----------------------------------------------------------------------------
 */

/**
 * 返回CONFIG定义的值
 * 
 * @param type $p_name	键值
 * @param type $p_key	设置值 
 */
function GC($p_name, $p_key) {
	$a = C($p_name);
	return isset($a[$p_key]) ? $a[$p_key] : '';
}

/**
 * 获取后台配置的参数
 */
function Conf($p_identy, $p_type = 'base', $p_bossID = '')
{
	$condition = array(
		'type' => $p_type
	);
	$condition['user_id'] = (int) $p_bossID;
	if(!empty($p_identy)){
		$condition['identy'] = $p_identy;
	}
	
	if(!empty($p_identy)){
		$data = D('System')->where($condition)->find();
		return $data['attvalue'];
	}else{
		$datas	 = array();
		$list	 = D('System')->where($condition)->select();
		if (!empty($list)) {
			foreach ($list as $v) {
				$datas[$v['identy']] = $v['attvalue'];
			}
		}
		return $datas;
	}
}

//htmlspecialchars_decode简写
function HD($string) {
	return htmlspecialchars_decode($string);
}

/**
 * 获取'price'属性下最大价格
 * @param mixed $choosedArr
 * @return float $max
 */
function get_max_price($choosedArr) {
	$max = 0;
	foreach ($choosedArr['price'] as $roomprices) {
		foreach ($roomprices as $price) {
			if ($price > $max)
				$max = $price;
		}
	}
	return $max;
}

/**
 * 获取拼音首字母
 */
function get_pinyin($name) {
	import('@.ORG.Util.Pinyin');

	$py			 = Pinyin::instance();
	$initials	 = $py->get($name);
//	han4 zi4 zhong1 wen2 a b c 1 2 3 - = +
	$pieces		 = explode(" ", $initials);
	$str		 = '';
	foreach ($pieces as $rs) {
		if ($rs == 'sha4')
		{
			$str .= 'x';
		}
		else
		{
			$str .= $rs[0];
		}
	}
	return $str;
}

/**
 * 返回加密密码
 *
 * @param String	$p_password		原密码
 * @param Int		$p_key			标识码
 * @return String	新密码
 */
function get_pass($p_password, $p_key = 'suncco_key') {
//	$newpass = md5($p_password . md5($p_key));
	return encrypt_pwd($p_password);
}

/**
 * 返回从今天开始之后几天
 *
 * @param String	$p_date
 * @param Int		$p_day
 * @return String
 */
function getLaterDay($p_date, $p_day) {
	$now	 = strtotime($p_date);
	$m		 = $p_day * 24 * 60 * 60;
	$diff	 = $now + $m;
	$oldday	 = date("Y-m-d", $diff);
	return $oldday;
}

/**
 * 输出中文格式日期时间[2012-02-02 星期一]
 *
 * @param Int		$p_num		当前时间之后或之前几天[负为前几天]
 * @param Boolean	$p_isdesc	是否显示星期
 * @return type
 */
function getCnDay($p_num = 0, $p_isdesc = true) {
	$time	 = time() + 60 * 60 * 24 * $p_num;
	$day	 = date("Y-m-d", $time);
	$desc	 = $p_isdesc ? ' ' . getCnWeek($time) : '';
	return $day . $desc;
}

/**
 * 输出指定时间戳的中文星期
 *
 * @param Int		$p_time		时间
 * @return String
 */
function getCnWeek($p_time) {
	$week[1] = '星期一';
	$week[2] = '星期二';
	$week[3] = '星期三';
	$week[4] = '星期四';
	$week[5] = '星期五';
	$week[6] = '星期六';
	$week[0] = '星期日';
	return $week[date('w', $p_time)];
}

/**
 * 字符截取 支持UTF8/GBK
 *
 * @param String	$string		要截取的字符串
 * @param Int		$length		截取长度
 * @param String	$dot		截取成功，增加标识“...”等
 * @return String	截取后的字符串
 */
function cut_str($string, $length, $dot = '...') {
	$strlen	 = strlen($string);
	if ($strlen <= $length)
		return $string;
	$string	 = str_replace(array(' ', '&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array('∵', ' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
	$strcut	 = '';
	if (strtolower('utf-8') == 'utf-8')
	{
		$length	 = intval($length - strlen($dot) - $length / 3);
		$n		 = $tn		 = $noc	 = 0;
		while ($n < strlen($string)) {
			$t = ord($string[$n]);
			if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126))
			{
				$tn = 1;
				$n++;
				$noc++;
			}
			elseif (194 <= $t && $t <= 223)
			{
				$tn = 2;
				$n += 2;
				$noc += 2;
			}
			elseif (224 <= $t && $t <= 239)
			{
				$tn = 3;
				$n += 3;
				$noc += 2;
			}
			elseif (240 <= $t && $t <= 247)
			{
				$tn = 4;
				$n += 4;
				$noc += 2;
			}
			elseif (248 <= $t && $t <= 251)
			{
				$tn = 5;
				$n += 5;
				$noc += 2;
			}
			elseif ($t == 252 || $t == 253)
			{
				$tn = 6;
				$n += 6;
				$noc += 2;
			}
			else
			{
				$n++;
			}
			if ($noc >= $length)
			{
				break;
			}
		}
		if ($noc > $length)
		{
			$n -= $tn;
		}
		$strcut	 = substr($string, 0, $n);
		$strcut	 = str_replace(array('∵', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), array(' ', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), $strcut);
	}
	else
	{
		$dotlen		 = strlen($dot);
		$maxi		 = $length - $dotlen - 1;
		$current_str = '';
		$search_arr	 = array('&', ' ', '"', "'", '“', '”', '—', '<', '>', '·', '…', '∵');
		$replace_arr = array('&amp;', '&nbsp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;', ' ');
		$search_flip = array_flip($search_arr);
		for ($i = 0; $i < $maxi; $i++) {
			$current_str = ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
			if (in_array($current_str, $search_arr))
			{
				$key		 = $search_flip[$current_str];
				$current_str = str_replace($search_arr[$key], $replace_arr[$key], $current_str);
			}
			$strcut .= $current_str;
		}
	}
	return $strcut . $dot;
}

/**
 * 过滤参数ID为数字的，非数字时提示错误
 *
 * @param Int	$p_str		参数
 * @return Int	返回正常数字
 */
function isNum($p_str) {
	if (!is_numeric($p_str))
	{
		halt('对不起，参数有误！');
	}
	$Num = trim($p_str);
	return $Num;
}

/**
 * 检查是否在串中，串以逗号间隔“x,x,x,x”
 * 不在时，提示错误，否则返回串
 *
 * @param String $p_str1		需要检查的字符串
 * @param String $p_str2		被检查的串
 * @return String
 */
function isExist($p_str1, $p_str2) {
	$arr = explode(',', $p_str2);
	if (!in_array($p_str1, $arr))
	{
		halt('对不起，参数有误！');
	}
	return $p_str1;
}

/**
 * 删除指定文件夹
 *
 * @param String $dir	真路径
 * @return boolean
 */
function delDir($p_dir) {
	$dh		 = opendir($p_dir);
	while ($file	 = readdir($dh)) {
		if ($file != "." && $file != "..")
		{
			$fullpath = $p_dir . "/" . $file;
			if (!is_dir($fullpath))
			{
				unlink($fullpath);
			}
			else
			{
				delDir($fullpath);
			}
		}
	}
	closedir($dh);
	if (rmdir($p_dir))
	{
		return true;
	}
	else
	{
		return false;
	}
}

/**
 * 对象转数组
 *
 * @param Object $p_object	对象
 * @return Array
 */
function objectToArray($p_object) {
	$_array = is_object($p_object) ? get_object_vars($p_object) : $p_object;
	foreach ($_array as $key => $value) {
		$value		 = (is_array($value) || is_object($value)) ? objectToArray($value) : $value;
		$array[$key] = $value;
	}
	return $array;
}

/**
 * 检查是否使用手机上网
 *
 * @return boolean
 */
function isWap() {
	$useragent	 = $_SERVER['HTTP_USER_AGENT'];
	$serverhost	 = $_SERVER['HTTP_HOST'];
	$DomainArray = explode('.', $serverhost);
	$FirstDomain = $DomainArray[0];
	if (in_array($FirstDomain, array('wap', 'w', 'm', '3g')))
	{//WAP上的
		return true;
	}
	elseif (preg_match("/(Android|iPhone)/", $useragent))
	{
		return true;
	}
	else
	{
		return false;
	}
}

/**
 * 判断是否为机器人
 *
 * @param String $p_browerType	浏览器类型，不填时，自动获取
 * @return Boolean
 */
function isRoot($p_browerType = '') {
	$p_browerType	 = $p_browerType ? $p_browerType : $_SERVER['HTTP_USER_AGENT'];
	$robot			 = false;
	if ($p_browerType)
	{
		$spiders = 'Sosospider|Googlebot|Baiduspider|spider|Spider|Ezooms';
		if (preg_match("/($spiders)/", $p_browerType))
		{
			$robot = true;
		}
		else
		{
			$robot = false;
		}
	}
	return $robot;
}

/**
 * 加密方法
 *
 * @param String $p_string	要加密的字符串
 * @return String
 * @deprecated
 */
function encrypt_pwd($p_string) {
	//TODO 为前端加密预留，可扩展
	$p_string = md5($p_string);
	return md5(crypt($p_string, substr($p_string, 0, 2)));
}

/**
 * 字符串加密算法
 *
 * @param <type> $string     需要加密的字符串
 * @param <type> $operation 操作(true解码 | false编码), 默认为 true
 * @param <type> $authKey    唯一KEY值
 * @param <type> $expiry
 * @return <type>
 */
function authcode($string, $operation = true, $authKey = '', $expiry = 0) {
	$operation	 = $operation ? 'DECODE' : 'ENCODE';
	$ckey_length = 5;
	//加密时随机加上字符
	if ($operation == 'ENCODE')
	{
		$string .= "@@@" . rand(100, 1100);
	}
	//当为解密时，需要把 @@替换为'/'@替换为'+'
	if ($operation == 'DECODE')
	{
		$string	 = str_replace('CrOsSsS', '/', $string);
		$string	 = str_replace('DoMaInNn', '+', $string);
	}
	$authKey = md5($authKey ? $authKey : AUTHKEY );
	$keya	 = md5(substr($authKey, 0, 16));
	$keyb	 = md5(substr($authKey, 16, 16));
	$keyc	 = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey	 = $keya . md5($keya . $keyc);
	$key_length	 = strlen($cryptkey);

	$string			 = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
	$string_length	 = strlen($string);

	$result	 = '';
	$box	 = range(0, 255);

	$rndkey = array();
	for ($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for ($j = $i = 0; $i < 256; $i++) {
		$j		 = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp	 = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for ($a = $j	 = $i	 = 0; $i < $string_length; $i++) {
		$a		 = ($a + 1) % 256;
		$j		 = ($j + $box[$a]) % 256;
		$tmp	 = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if ($operation == 'DECODE')
	{
		if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16))
		{
			return substr($result, 26);
		}
		else
		{
			return '';
		}
	}
	else
	{
		//获得最终加密后的值
		$deString	 = $keyc . str_replace('=', '', base64_encode($result));
		//当为解密时，需要把 @@替换为'/'@替换为'+'
		$deString	 = str_replace(array('/', '+'), array('CrOsSsS', 'DoMaInNn'), $deString);
		return $deString;
	}
}

/**
 * 通过加密后的字符串获取原始值
 * @param <type> $string
 */
function getAuthCode($string, $authKey = '') {
	$parmTmp	 = authcode($string, 'DECODE', $authKey);
	$parmTmpArr	 = explode("@@@", $parmTmp);
	return $parmTmpArr[0];
}

/**
 * 获取页面传递过来的参数
 * @param <type> $parmStr   参数名称
 * @param <type> $intCheck  是否为数字[0字符，1数字]
 * @param <type> $authCodeCheck  是否需要解密[0不需要，1需要]
 * @param <type> $postType  传递类型['POST', 'GET', 'REQUEST']
 * @param <type> $escape    是否对参数进行反js escape解码(用于url中文参数传递)　[0不需要，1需要]
 */
function getRequestData($parmStr, $intCheck = '0', $authCodeCheck = '0', $postType = 'REQUEST', $escape = '0') {
	$postArr	 = $postType == 'POST' ? $_POST : ( $postType == 'GET' ? $_GET : $_REQUEST );
	$parmValue	 = isset($postArr[$parmStr]) ? $postArr[$parmStr] : '';
	$parmValue	 = trim($parmValue);
	//解密算法
	if ($authCodeCheck)
	{
		$parmTmp	 = getDcAuthCode($parmValue);
		$parmValue	 = isset($parmTmp) ? $parmTmp : '';
	}
	//escape解码
	if ($escape)
	{
		$parmTmp	 = js_unescape($parmValue);
		$parmValue	 = isset($parmTmp) ? $parmTmp : '';
	}
	$parmValue = $intCheck ? fmlReplace($parmValue) : $parmValue;
	return $parmValue;
}

/**
 * 返回JSON数据
 *
 * @param <int>		$p_state			状态[0,1]
 * @param <array>	$p_reMsg			消息1
 * @param <array>	$p_reMsgSc			消息2
 */
function ajaxReturnMsg($p_state, $p_reMsg, $p_reMsgSc = '') {
	//echo $reMsg;
	echo json_encode(array('state' => $p_state, 'reMsg' => $p_reMsg, 'reMsgSc' => $p_reMsgSc));
	exit();
}

/**
 * 获取基础域名
 */
function get_base_domain() {
	$base_domain = $_SERVER['HTTP_HOST'];
// 	if (false === strpos($_SERVER['HTTP_HOST'], 'www') && substr_count($_SERVER['HTTP_HOST'], '.') == 2) {
// 		$base_domain = substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.') + 1);
// 	}
//	$base_domain = "http://".$base_domain.":88";
	$base_domain = "http://" . $base_domain;
	return $base_domain;
}

/**
 * 根据值获取状态的名称
 * @param integer $value 值
 * @param string $type 状态的类型
 */
function to_state($value, $type) {
	$map = array(
		'week'	 => array(1 => '星期一', 2 => '星期二', 3 => '星期三', 4 => '星期四', 5 => '星期五', 6 => '星期六', 0 => '星期天'),
		'order'	 => array(1 => '预定未确认', 2 => '预定已确认', 3 => '预定自动取消', 4 => '预定人工取消', 11 => '预定转入住', 12 => '直接入住', 13 => '钟点入住', 14 => '续住', 15 => '提前结账退房', 17 => '临时挂账', 18 => '结账退房', 19 => '逃单'),
	);
	return $map[$type][$value];
}

/**
  +----------------------------------------------------------
 * 把返回的数据集转换成Tree,并将$pk 作为key
  +----------------------------------------------------------
 */
function list_to_tree_key($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0, $field = 'id') {
	// 创建Tree
	$tree = array();
	if (is_array($list))
	{
		// 创建基于主键的数组引用
		$refer = array();
		foreach ($list as $key => $data) {
			$refer [$data [$pk]] = & $list [$key];
		}
		foreach ($list as $key => $data) {
			// 判断是否存在parent
			$parentId = $data [$pid];
			if ($root == $parentId)
			{
				$tree [$data [$field]] = & $list [$key];
			}
			else
			{
				if (isset($refer [$parentId]))
				{
					$parent								 = & $refer [$parentId];
					$parent [$child] [$data [$field]]	 = & $list [$key];
				}
			}
		}
	}
	return $tree;
}

/**
 * 从用户名中获得简写的用户名
 *
 * @param string $p_str		字符串
 */
function simpleName($p_str) {
	$tmpArr = explode('@', $p_str);
	return $tmpArr[0];
}

/**
 * 短信验证
 * @param  string	$p_smscode 验证码
 * @return boolean
 */
function checkSmsCode($p_smscode) {
	import("Sms", 'Public/Class/suncco/');
	$Sms	 = new Sms();
	$smscode = $Sms->getSendRand();
	if ($p_smscode == $smscode)
	{
		$Sms->DelRandCode();
		return true;
	}
	else
	{
		return false;
	}
}

/**
 * 从完整路径中获得图片名称
 * 
 * @param  string	$p_path 图片名称
 */
function get_imagename_from_path($p_path) {
	$tmp = explode('/', $p_path);
	return $tmp[count($tmp) - 1];
}

/**
 * 根据最大限制获得图片长宽。
 * 
 * @param  string	$p_path 图片名称
 */
function get_image_spec($p_path, $p_w, $p_h) {
	if (!file_exists(ROOT . $p_path))
	{
		return false;
	}

	$imageArr = getimagesize(ROOT . $p_path);
	if (!$imageArr)
	{
		return false;
	}
	//如果长比宽高。则使用最宽
	if ($imageArr[0] > $imageArr[1])
	{
		return array(
			'width'	 => $p_w,
			'height' => ceil($p_w / $imageArr[0] * $imageArr[1])
		);
	}
	else
	{
		return array(
			'width'	 => ceil($p_h / $imageArr[1] * $imageArr[0]),
			'height' => $p_h
		);
	}
}

/**
 * 文件写入函数
 * 
 * @param String $path 文件路径
 * @param String $content 文件内容
 */
function file_write($fileName, $content, $wType = 'w+') {
	$fp = @fopen($fileName, $wType);
	if (!$fp)
	{
		return false;
	}
	if (flock($fp, LOCK_EX))
	{
		fwrite($fp, $content, strlen($content));
		flock($fp, LOCK_UN);
		fclose($fp);
		return true;
	}
	else
	{
		return false;
	}
}

/**
 * 自动生成路径的目录
 * 
 * @param string $path	路径
 * @param string $mode	权限
 */
function mkdirs($path, $mode = '0664') {
	$adir	 = explode('/', $path);
	$dirlist = '';
	$rootdir = array_shift($adir);
	if (($rootdir != '.' || $rootdir != '..') && !file_exists($rootdir))
	{
		@mkdir($rootdir);
	}
	foreach ($adir as $key => $val) {
		if ($val != '.' && $val != '..')
		{
			$dirlist .= "/" . $val;
			$dirpath = $rootdir . $dirlist;
			if (!file_exists($dirpath))
			{
				mkdir($dirpath);
				chmod($dirpath, 0777);
			}
		}
	}
}

/**
 * 通过CURL以POST发起请求，支持代理
 * 
 * @param string $url			地址
 * @param string $postfield		要提交的字段
 * @param string $proxy			代理设置[127.0.0.1:808]
 * @return string				返回的信息
 */
function curl_request($url, $postfield, $proxy = "") {
	$proxy		 = trim($proxy);
	$user_agent	 = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; SV1)";
	$ch			 = curl_init(); // 初始化CURL句柄
	if (!empty($proxy))
	{
		curl_setopt($ch, CURLOPT_PROXY, $proxy); //设置代理服务器
	}
	curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
	//curl_setopt($ch, CURLOPT_FAILONERROR, 1); // 启用时显示HTTP状态码，默认行为是忽略编号小于等于400的HTTP信息
	//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);//启用时会将服务器服务器返回的“Location:”放在header中递归的返回给服务器
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 设为TRUE把curl_exec()结果转化为字串，而不是直接输出
	curl_setopt($ch, CURLOPT_POST, 1); //启用POST提交
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfield); //设置POST提交的字符串
	//curl_setopt($ch, CURLOPT_PORT, 80); //设置端口
	curl_setopt($ch, CURLOPT_TIMEOUT, 25); // 超时时间
	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent); //HTTP请求User-Agent:头
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Accept-Language: zh-cn',
		'Connection: Keep-Alive',
		'Cache-Control: no-cache'
	));
	//设置HTTP头信息
	$document	 = curl_exec($ch); //执行预定义的CURL
	$info		 = curl_getinfo($ch); //得到返回信息的特性
	//print_r($info);
	if ($info[http_code] == "405")
	{
		return "bad proxy {$proxy}\n";  //代理出错
	}
	//curl_close($ch);
	return $document;
}

/**
 * 
 * +------------------------------------------------------------+
 * @name html_editor
 * +------------------------------------------------------------+
 * 调用副文本编辑器
 * +------------------------------------------------------------+
 *
 * @author anzai <sba3198178@126.com>
 * @version 1.0
 *
 * @example
 *
 * @param string	$p_fileType	要上传的文件类型[image|file|flash|media]
 * @param string	$p_name		内容ID
 * @param string	$p_value	默认内容
 * @param int		$p_width	宽度
 * @param int		$p_height	高度
 * @param string	$p_bar		编辑使用的控件序列[默认default]
 *
 */
function html_editor($p_fileType, $p_name = 'content', $p_value = '', $p_width = '99%', $p_height = 220, $p_bar = 'default', $loaded = false) {
	static $toolbar							 = null, $load				 = false, $idx	 = 0;
	if ($toolbar === null)
	{
		$toolbar = require_once ROOT . 'Conf/Logic/toolbar.cfg.php';
	}
	$basePath	 = '/Public/Js/ueditor/';
	$jsvar		 = 'FORM_EDITORS[' . $idx . ']';
	$idx++;
	$item = isset($toolbar[$p_bar]) ? $toolbar[$p_bar] : $toolbar['default'];
	$html = '';
	if ($load === false && $loaded === false){
		$html = "<script>if(!UEDITOR_HOME_URL){var UEDITOR_HOME_URL = '/Public/Js/ueditor/';}</script>";
		$html .= '<script charset="utf-8" src="'. $basePath .'ueditor.config.js?v='. C('VERSION') .'"></script>';
		$html .= '<script charset="utf-8" src="'. $basePath .'ueditor.all.min.js"></script>';
		$html .= '<script charset="utf-8" src="'. $basePath .'lang/zh-cn/zh-cn.js"></script>';
		if(strpos($item, '"wxeditor"') !== false){
			$html .= '<script charset="utf-8" src="'. $basePath .'plugin.js?v='. C('VERSION') .'"></script>';
		}
		$load	 = true;
	}
	$html .= '<script id="' . $p_name . '" name="' . $p_name . '" style="width:' . $p_width . ';height:' . $p_height . 'px;"  type="text/plain">' . htmlspecialchars_decode($p_value) . '</script>';

	$html .= '<script>
				if(!FORM_EDITORS){var FORM_EDITORS = [];var LAST_FORM_EDITOR = NULL;}
				' . $jsvar . ' = UE.getEditor("' . $p_name . '", {
					topOffset: 50,
					autoFloatEnabled: false,
					autoWidthEnabled: true,
					autoHeightEnabled: false,
					autotypeset: {
						removeEmptyline: true
					},
					toolbars: ' . $item . '
				});
				</script>
			';
	return $html;
}

/**
 * 文件上传类实例化
 * 
 * @param type $url
 * @param type $vars
 * @param type $layer
 * @return boolean
 */
function upload($p_id, $p_value, $p_path, $p_multi = 0, $p_t = '选择图片', $p_ext = 'img_ext') {
	if (empty($p_path))
	{
		echo '未知路径';
		return false;
	}
	$_GET['action_name'] = "file";
	$class				 = A('Upload');
	return $class->run(array(
				'id'			 => $p_id,
				'name'			 => $p_id,
				'value'			 => $p_value,
				'path'			 => $p_path,
				'multi'			 => $p_multi,
				'title'			 => $p_t,
				'ext'			 => $p_ext
	));
}

function repeat($mark, $num, $string = false, $glue = ',') {
	$num		 = (int) $num;
	if ($num <= 0)
		return $string ? '' : array();
	for ($i = 0; $i < $num; $i++)
		$repeat[]	 = $mark;
	return $string ? implode($glue, $repeat) : $repeat;
}

/**
 *
 * +------------------------------------------------------------+
 * @name url
 * +------------------------------------------------------------+
 * 根据传入不同的参数和启用的url模式自动组装相应的url地址
 * +------------------------------------------------------------+
 *
 * @author anzai <sba3198178@126.com>
 * @version 1.0
 *
 * @example
 *
 * @param string $action 动作名，默认为index
 * @param string $module 控制器名，默认为当前当前所在控制器
 * @param mixed  $params 附带参数，格式可以是一个关联数组array(变量名=>值得)，如array('p'=>1,'a='3)；或一个字符串，如p=1&a=3
 *
 */
function url($action = ACTION_NAME, $module = MODULE_NAME, $params = '', $suffix = true, $redirect = false, $domain = false) {
	$module	 = empty($module) ? MODULE_NAME : $module;
	$action	 = empty($action) ? ACTION_NAME : $action;
	$url	 = $module . '/' . $action;
	return U($url, $params, $suffix, $redirect, $domain);
}

/**
 * 生成缩略图
 */
function thumb($p_url, $p_w, $p_h, $p_default = 1) {
	$url = D('Thumb')->create($p_url, $p_w, $p_h, 0, $p_default);
	return substr($url, 0, 1) != '/' ? '/' . $url : $url;
}

//根据经纬度计算距离 其中A($lat1,$lng1)、B($lat2,$lng2)
function getDistance($lat1, $lng1, $lat2, $lng2) {
	//地球半径
	$R = 6378137;

	//将角度转为狐度
	$radLat1 = deg2rad($lat1);
	$radLat2 = deg2rad($lat2);
	$radLng1 = deg2rad($lng1);
	$radLng2 = deg2rad($lng2);

	$a	 = $radLat1 - $radLat2; //两纬度之差,纬度<90
	$b	 = $radLng1 - $radLng2; //两经度之差纬度<180
	//结果
	//余弦定理以及弧度计算方法
	$s	 = acos(cos($radLat1) * cos($radLat2) * cos($radLng1 - $radLng2) + sin($radLat1) * sin($radLat2)) * $R;
	//谷歌开源距离计算方法
	$s	 = 2 * asin(sqrt(pow(sin(($radLat1 - $radLat2) / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin(($radLng1 - $radLng2) / 2), 2))) * $R;

	return round($s);
}

/**
 * @desc 根据两点间的经纬度计算距离
 * @param float $lat 纬度值
 * @param float $lng 经度值
 */
function getDistance_1($lat1, $lng1, $lat2, $lng2) {
	$earthRadius = 6367000; //approximate radius of earth in meters

	/*
	  Convert these degrees to radians
	  to work with the formula
	 */

	$lat1	 = ($lat1 * pi() ) / 180;
	$lng1	 = ($lng1 * pi() ) / 180;

	$lat2	 = ($lat2 * pi() ) / 180;
	$lng2	 = ($lng2 * pi() ) / 180;

	/*
	  Using the
	  Haversine formula

	  http://en.wikipedia.org/wiki/Haversine_formula

	  calculate the distance
	 */

	$calcLongitude		 = $lng2 - $lng1;
	$calcLatitude		 = $lat2 - $lat1;
	$stepOne			 = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
	$stepTwo			 = 2 * asin(min(1, sqrt($stepOne)));
	$calculatedDistance	 = $earthRadius * $stepTwo;

	return round($calculatedDistance);
}

/**
 * 数组分页函数  核心函数  array_slice 
 * 用此函数之前要先将数据库里面的所有数据按一定的顺序查询出来存入数组中 
 * $pagesize   每页多少条数据 
 * $page   当前第几页 
 * $array   查询出来的所有数组 
 */
function page_array($page, $pagesize, $array) {
	$page		 = (empty($page)) ? '1' : $page; #判断当前页面是否为空 如果为空就表示为第一页面   
	$start		 = ($page - 1) * $pagesize; #计算每次分页的开始位置  
	$pagedata	 = array_slice($array, $start, $pagesize);

	return $pagedata;
}

/**
 * 米转换成标准格式
 */
function chage_m($s) {
	if ($s < 1000)
	{
		$str = $s . "m";
	}
	else
	{
		$str = round($s / 1000, 1) . "km";
	}

	return $str;
}

/**
 * 过滤字符串
 */
function filter_str($str) {
	$str = strip_tags($str);
	$str = str_replace("&nbsp", '', $str);
	$str = str_replace("\\n", '', $str);
	$str = str_replace("　", '', $str);
	$str = str_replace("<br>", '', $str);

	return $str;
}

/**
 * 判断目标时间与当前时间差，给出分钟差、小时、天数差
 */
function longtime($time) {
	$now			 = time();
	$difftime_second = $now - $time;
	$difftime		 = ($now - $time) / 3600;
	if ($difftime_second < 60)
	{
		if ($difftime_second == 0)
		{
			$difftime_second = 1;
		}
		return $difftime_second . '秒前';
	}
	elseif ($difftime < 1)
	{
		$difftime	 = explode('.', $difftime);
		$difftime	 = intval(('0.' . $difftime[1]) * 60);
		return $difftime . '分钟前';
	}
	elseif ($difftime >= 1 && $difftime < 24)
	{
		return intval($difftime) . '小时前';
	}
	elseif ($difftime >= 24 && $difftime < 24 * 10)
	{
		$difftime = $difftime / 24;
		return intval($difftime) . '天前';
	}
	else
	{
		$datetime = date('Y-m-d H:i', $time);
		return $datetime;
	}
}

/*
 * 获取指定日期所在星期的开始时间与结束时间
 */

function getWeekRange($date) {
	$ret			 = array();
	$timestamp		 = strtotime($date);
	$w				 = strftime('%u', $timestamp);
	$ret['sdate']	 = date('Y-m-d 00:00:00', $timestamp - ($w - 1) * 86400);
	$ret['edate']	 = date('Y-m-d 23:59:59', $timestamp + (7 - $w) * 86400);
	return $ret;
}

/*
 * 获取指定日期所在月的开始日期与结束日期
 */

function getMonthRange($date) {
	$ret			 = array();
	$timestamp		 = strtotime($date);
	$mdays			 = date('t', $timestamp);
	$ret['sdate']	 = date('Y-m-1 00:00:00', $timestamp);
	$ret['edate']	 = date('Y-m-' . $mdays . ' 23:59:59', $timestamp);
	return $ret;
}

/*
 * 以上两个函数的应用
 */

function getFilter($n) {
	$ret = array();
	switch ($n) {
		case 1:// 昨天
			$ret['sdate']	 = date('Y-m-d 00:00:00', strtotime('-1 day'));
			$ret['edate']	 = date('Y-m-d 23:59:59', strtotime('-1 day'));
			break;
		case 2://本星期
			$ret			 = getWeekRange(date('Y-m-d'));
			break;
		case 3://上一个星期
			$strDate		 = date('Y-m-d', strtotime('-1 week'));
			$ret			 = getWeekRange($strDate);
			break;
		case 4: //上上星期
			$strDate		 = date('Y-m-d', strtotime('-2 week'));
			$ret			 = getWeekRange($strDate);
			break;
		case 5: //本月
			$ret			 = getMonthRange(date('Y-m-d'));
			break;
		case 6://上月
			$strDate		 = date('Y-m-d', strtotime('-1 month'));
			$ret			 = getMonthRange($strDate);
			break;
	}
	return $ret;
}

/**
 * 智能合并多个数组
 */
function extend($arr) {
	$args	 = func_get_args();
	$arr	 = array();
	if (!empty($args))
	{
		foreach ($args as $vo) {
			$vo	 = is_array($vo) ? $vo : (empty($vo) ? array() : array($vo));
			$arr = array_merge($arr, $vo);
		}
	}

	return $arr;
}

/**
 * in_array的别名，可智能判断$array是否为数组，不是数组则自动转换为数组
 */
function inArray($needle, $array) {
	return in_array($needle, is_array($array) ? $array : array($array));
}

/**
 * 生成微信菜单中跳转的链接
 * 
 * @param int		$p_openid		OPENID
 * @param string	$p_appId		微信APPID
 * @param string	$p_url			要跳转的URL
 * @param int		$p_cert			1认证2未认证
 */
function create_wx_menu_url($p_openid, $p_appId, $p_url, $p_cert = 1) {
	if ($p_cert == 1)
	{
		return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$p_appId}&redirect_uri={$p_url}&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
	}
	else
	{
		if (strpos($p_url, '?') != false)
		{
			return $p_url . '&openid=' . $p_openid;
		}
		else
		{
			return $p_url . '?openid=' . $p_openid;
		}
	}
}

/**
 * 
 * +------------------------------------------------------------+
 * @name array_to_json
 * +------------------------------------------------------------+
 * 将数组转换为json
 * +------------------------------------------------------------+
 *
 * @param array $array
 *
 */
function array_to_json($data){
	static $format_func = null;
	if ($format_func === null){
		//动态创建一个函数用于解析json数据
		$format_func = create_function('&$value', '
			if(is_bool($value)) {
            	$value = $value?\'true\':\'false\';
	        }elseif(is_int($value)) {
	            $value = intval($value);
	        }elseif(is_float($value)) {
	            $value = floatval($value);
	        }elseif(defined($value) && $value === null) {
	            $value = strval(constant($value));
	        }elseif(is_string($value)) {
	            $value = \'"\'.addslashes($value).\'"\';
	        }
	        return $value;
		');
	}
	
	if(is_object($data)) {
		//对象转换成数组
		$data = get_object_vars($data);
	}else if(!is_array($data)) {
		// 普通格式直接输出
		return $format_func($data);
	}
        // 判断是否关联数组
	if(empty($data) || is_numeric(implode('',array_keys($data)))) {
		$assoc  =  false;
	}else {
		$assoc  =  true;
	}
	// 组装 Json字符串
	$json = $assoc ? '{' : '[' ;
	foreach($data as $key=>$val) {
		if(!is_null($val)) {
			if($assoc) {
				$json .= "\"$key\":".array_to_json($val).",";
			}else {
				$json .= array_to_json($val).",";
			}
		}
	}
	if(strlen($json)>1) {// 加上判断 防止空数组
		$json  = substr($json,0,-1);
	}
	$json .= $assoc ? '}' : ']' ;
	return $json;
}

/**
 * 创建微信的URL
 * 
 * @param type $p_method
 * @param type $p_param
 */
function WU($p_method, $p_param, $p_token=''){
	if(defined('TOKEN') && empty($p_token)){
		$p_token = TOKEN;
	}
    if(empty($p_param)){
        $p_param['token'] = $p_token;
    }else{
        if(is_string($p_param)){
            $p_param .= '&token='. $p_token;
        }
        elseif(!isset($p_param['token'])){
            $p_param['token'] = $p_token;
        }
    }
	
    return U($p_method, $p_param, true, false, true, 'wap.php');
}

/**
 * 通过字符串获取商品编号
 * 
 * @param type $p_str
 */
function get_goods_code($p_str)
{
	$b = preg_match("/[\x{4e00}-\x{9fa5}]/u", $p_str, $result);
	
	if(!$b){
		return $p_str;
	}
	
	return msubstr($p_str, 0, strpos($p_str, $result[0]), 'utf-8', '');
}
?>
