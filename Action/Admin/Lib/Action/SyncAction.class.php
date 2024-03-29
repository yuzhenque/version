<?php

/**
 * 
 * 同步管理
 *
 * @author bluefoot<bluefoot@qq.com>
 */
class SyncAction extends CommonAction
{

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$Model = D('Server');


		$list = $Model->select();

		$this->assign('list', $list);
		$this->display();
	}

	public function do_sync()
	{
		$server_ids	 = $this->_POST('server_ids');
		$sync_files	 = $this->_POST('sync_files');
		$svn_update	 = (int) $this->_POST('svn_update');
		if (empty($sync_files) || empty($server_ids)) {
			$this->error('无文件或未选择服务器');
		}
		$sync_files = explode("\r\n", trim($sync_files));

		$server_list = D('Server')->where(array(
					'server_id' => array('IN', $server_ids)
				))->select();
		if (empty($server_list)) {
			$this->error('无服务器');
		}

		$log = $svn_up = array();
		foreach ($server_list as $k => $v) {
			
			
			if($svn_update == 1 && empty($svn_up[md5($sync_path)])){
				@ini_set('display_errors', 'On');
				error_reporting(E_ALL);
//				$a = exec("dir", $out, $status);
//				print_r($status);
//				print_r($out);
//				print_r($status);
//				exit;
				
				$sync_path = str_replace('/', '\\', $sync_path);
				exec('C:\Progra~1\SlikSvn\bin\svn.exe update '. $sync_path, $tmp, $ret);// .' 2>&1 2>&1'
				$svn_up[md5($sync_path)] = implode('<br/>', $tmp);
				dump('C:\Progra~1\SlikSvn\bin\svn.exe update '. $sync_path . ' --username=wuxr490 --password=eLic3TnH4kr3dbH');
				dump($tmp);
				dump($ret);
				exit;
			}
			
			if($v['server_sync_type'] == 1) {
				$connection = ssh2_connect($v['server_ip'], $v['server_port']);
				if (!$connection) {
					$this->error("{$v['server_title']} 无法连接");
				}
				$check = ssh2_auth_password($connection, $v['server_account'], $v['server_password']);
				if (!$check) {
					$this->error("{$v['server_title']} 用户名或密码不正确");
				}
				$sftp        = ssh2_sftp($connection);
			}else{
				$connection = ftp_connect($v['server_ip'], $v['server_port'], 5);
				ftp_login($connection, $v['server_account'], $v['server_password']);
				ftp_pasv($connection,true);
			}
			
			$sync_path   = trim($v['server_local_path']);
			$server_path = trim($v['server_sync_path']);
			
			foreach ($sync_files as $file) {
				$file = str_replace(array("file://", $sync_path), '', trim($file));
				
				if (!file_exists(str_replace('//', '/', $sync_path . $file))) {
					$log[] = "{$v['server_title']} {$file} 不存在";
					$this->error("{$v['server_title']} {$file} 不存在");
				}


				if($v['server_sync_type'] == 1) {
					$stream      = @fopen("ssh2.sftp://{$sftp}" . str_replace('//', '/', $server_path . $file), 'w');
					$writeResult = @fwrite($stream, @file_get_contents(str_replace('//', '/', $sync_path . $file)));
					@fclose($stream);
				}else{
					$writeResult = ftp_put($connection, str_replace('//', '/', $server_path . $file), str_replace('//', '/', $sync_path . $file), FTP_BINARY);
				}
				if ($writeResult == false) {
					$log[] = "{$v['server_title']} {$file} 失败";
					$this->error("{$v['server_title']} {$file} 失败");
				}
				$log[] = "{$v['server_title']} {$file} 成功";
			}
			@ftp_close($connection);
		}
		
		$this->success('同步成功', "<js>$('#sync_files_log').html('". implode('<br/>', $log) .'<br/>'. addslashes(implode('<br/>', $svn_up)) ."');</js>");
	}

	
	public function script()
	{
		$server_list = array(
//            array('内网测试服', 'http://192.168.9.15:932/'),
//            array('外网测试服', 'http://erp.e10.cncn.net/'),
            array('e005服务器', 'http://erp.d05.cncn.net/'),
            array('e007服务器', 'http://erp.derp.cncn.net/'),
            array('d08服务器', 'http://erp.d08.cncn.net/'),
            array('d08-2服务器', 'http://wxxy.d08.cncn.net/'),
            array('d09服务器', 'http://erp.d09.cncn.net/'),
            array('d10服务器', 'http://erp.d10.cncn.net/'),
            array('d11服务器', 'http://erp.d11.cncn.net/'),
            array('d12服务器', 'http://erp.d12.cncn.net/'),
            array('d15服务器', 'http://erp.d15.cncn.net/'),
            array('d16服务器', 'http://erp.d16.cncn.net/'),
            array('d18服务器', 'http://erp.d18.cncn.net/'),
//            array('建发测试服', 'http://th.cndits.net:8009/'),
            array('建发正式服', 'http://h.cndits.com/'),
            array('苏州文旅', 'http://erp.stotd.net/'),
            array('广州东旅', 'http://gzdl.derp.cncn.net/'),
            array('北京祥瑞', 'http://erp.59166.com/'),
            array('厦门易游', 'http://erp.topetravel.com/'),
            array('湖南金桔', 'http://erp.ijjly.com/'),
            array('乌鲁木齐铁道', 'http://erp.xj-ur.com/'),
//            array('湖南佳游', 'http://erp.jiayouguolv.com/'),
            array('两岸国旅', 'http://erp.cncsit.com/'),
            array('贵阳中旅', 'http://gyzl.derp.cncn.net/'),
//            array('上铁国旅', 'https://stgl.jk.railshj.com:932/'),
        //    array('贵州海外', 'http://gzhw.derp.cncn.net/'),
            array('上海世航', 'http://erp.shglb2b.cn/'),
            array('西美', 'http://erp.ximeizhilv.cn/'),
//            array('莫愁', 'https://plat.mochouu.com/'),
            array('西班牙三和', 'http://erp.holatrip.com/'),
            array('苏州舜天', 'http://www.stlxs.cn/'),
            array('芙蓉假期', 'http://erp.furongjiaqi.com/'),
            array('遵义旅行社', 'http://zylxs.d08.cncn.net/'),
            array('银河国旅', 'http://erp.ntyhlx.com/'),
//            array('山东银座', 'http://erp.4008161111.com/'),
            array('甘肃旅控', 'http://erp.gslykg.com/'),
//            array('上海乐视', 'http://erp.yueshilvyou.com/'),
            array('厦门智赢', 'http://erp.travel.pwgci.com/'),
            array('山西红马', 'http://hongma.serp.cncn.net/'),
//            array('成都铁路', 'http://cdtl.d12.cncn.net/'),
            array('福建超越未来', 'http://erp.cywlly.com/'),
            array('广东中旅', 'http://gdzl.serp.cncn.net/'),
            array('安徽中青', 'http://erp.cytsah.com/'),
            array('国旅环球', 'http://erp.citsglobe.com/'),
		);
		
		$this->assign('server_list', $server_list);
		$this->display();
	}

	public function getUrlHtml(){
	    $url = $this->_POST('url');
	    $html = file_get_contents($url);
	    return $this->success($html);
    }
}

?>