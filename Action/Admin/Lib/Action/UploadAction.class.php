<?php
/**
 * 上传文件挂件，调用方式
 * {:R('Upload/file', array('ext'=>'img_ext'), 'Action')}
 */
class UploadAction extends CommonAction {
	
	/**
	 * 文件类别及扩展名
	 */
	private $_file_ext = array(
		//所有扩展名
		'all_ext' => array(
			'ext'=>'*.*',
			'ext_name'=>array(),
		),
		//图片扩展名
		'img_ext' => array(
			'ext'=>'*.jpg;*.jpeg;*.png;*.gif;*.ico;*.bmp',
			'ext_name'=>array('bmp','jpg','jpeg','png','gif','ico'),
		),
		//xls扩展名
		'xls_ext' => array(
			'ext'=>'*.xls',
			'ext_name'=>array('xls'),
		),
		//证书pem扩展名
		'pem_ext' => array(
			'ext'=>'*.pem',
			'ext_name'=>array('pem'),
		),
	);
	
	/**
	 * 默认上传路径
	 */
	private $_file_path = 'Uploads/';
	
	/**
	 * 上传控件初始化配置
	 */
	private $_config = array(
		'title'			=> '',//控件标题
		'ext'			=> 'all_ext',//文件扩展名，对应$_file_ext中的键值
		'action_name'	=> 'file',//方法名
		'win'			=> 1,//是否显示上传文件弹出框
		'path'			=> 'file',//文件上传路径
		'id'			=> 'file_id',//表单域id
		'name'			=> 'file_id',//表单域变量名
		'multi'			=> false,//是否允许同时上传多个文件
	);
	
	/**
	 * 第一次上传
	 */
	private $_firstTime = false;
	
	
	/**
	 * 打开界面
	 */
	public function run($params=NULL){
		$params = extend($_GET, $params);
		//是否显示上传控件
		if ($this->_get('win')){
			$this->assign(array(
				'show_control'=>true,//显示上传图片框
				'controller_id'=>'uploadify-'.time(),//控制器id
			));
		}
		
		//用户配置和组件默认配置信息进行合并
		$this->_config = extend($this->_config, $params);
		$this->_config['id'] = str_replace(array('[', ']', '\'', '"'), '_', $this->_config['name']);
		$this->assign('params', $this->_config);
		
		if ($this->_firstTime === FALSE){
			$this->_firstTime = true;
			$this->assign('first_time', true);
		}else{
			$this->assign('first_time', false);
		}
		$action_name = $this->_get('action_name');
		$this->$action_name();
	}
	
	/**
	 * 上传控件初始化
	 */
	public function file(){
		$exts = $this->_file_ext[$this->_config['ext']]['ext'];
		$title = $this->_config['title'] ? $this->_config['title'] : '选择文件';
		//获取文件
		if ($this->_config['value']){
			$this->assign('datas', $this->_getFile($this->_config['value']));
		}
		$this->assign(array(
			'title'=>$title,
			'exts'=>$exts,
		));
		$this->display('Upload:index');
	}
	
	/**
	 * 上传文件处理，VAR_AJAX_SUBMIT=>cprs表示是Ajax提交方式
	 */
	public function index(){
		if (count($_FILES) > 0){
			import('UploadFile', 'Public/Class/Util/');
			$savepath = $this->_config['path'].'/'.date('Ym').'/';
			$upload = new UploadFile();
			$upload->savePath = $this->_file_path . $savepath;
			
			//文件上传成功后将信息保存到数据库中
			if ($upload->upload()){
				$saveinfo = $upload->getUploadFileInfo();
				if(!empty($saveinfo)){
					$data = array(
						'boss_id'	 => BOSS_ID,
						'name' 		 => $saveinfo[0]['name'],//原始文件名
						'file_name'  => $saveinfo[0]['savename'],//保存文件名
						'file_size'  => $saveinfo[0]['size'],//文件名大小
						'file_ext'   => $saveinfo[0]['extension'],//文件扩展名
						'save_path'	 => $saveinfo[0]['savepath'],//保存文件目录
						'file_path'  => $saveinfo[0]['savepath'].$saveinfo[0]['savename'],//保存文件路径
						'root_path'  => '/'.$saveinfo[0]['savepath'].$saveinfo[0]['savename'],//保存文件根目录
						'upload_time'=> time(),//上传时间
						'action_user'=> $this->_surename,//操作人
					);
					$model = D('File');
					//插入到数据库中
					if ($model->add($data)){
						$isimg = in_array($data['file_ext'], $this->_file_ext['img_ext']['ext_name']) ? 1 : 0;//是否为图片
						$this->success(array(
							'id'	=> $model->getLastInsID(),
							'path'	=> $data['root_path'],//图片根目录
							'image' => $isimg,//是否为图片
							'pos'	=> $isimg ? getPos($data['file_path']) : 0,//获取图片位置
						));
					}else{
						$this->error('系统出错，上传文件失败');
					}
				}else{
					$this->error('系统出错，上传文件失败');
				}
			}else{
				$this->error($upload->getErrorMsg());
				//$this->error('系统出错，上传文件失败');
			}
		}else{
			$this->error('没有选择任何文件');
		}
	}
	
	/**
	 * 上传图片
	 */
	public function image(){
		$ext = isset($this->_config['ext']) ? $this->_config['ext'] : 'img_ext';
		$exts = $this->_file_ext[$ext]['ext_name'];
		$title = $this->_config['title'] ? $this->_config['title'] : '选择图片';
		$this->assign(array(
			'title'=>$title,
			'exts'=>  implode(',', $exts),
		));
		if ($this->_config['value']){
			$this->assign('datas', $this->_getFile($this->_config['value']));
		}
		$this->display('Upload:index');
	}
	
	/**
	 * 查看历史上传
	 */
	public function history(){
		$model = D('File');
		$ext_name = $this->_file_ext[$this->_config['ext']]['ext_name'];//文件扩展名过滤
		if (!empty($ext_name)){
			$where['file_ext'] = array('IN', implode(",", $ext_name));
		}
		$where['boss_id'] = BOSS_ID;
		$count = $model->where($where)->count();
		if ($count > 0){
			$datas = $model->where($where)->limit(0,100)->order('upload_time DESC')->select();
			$this->assign(array(
				'datas'=>$datas,
				'image_exts'=>$this->_file_ext['img_ext']['ext_name'],
			));
		}
		
		$this->display('Upload:history');
	}
	
	/**
	 * 删除文件
	 */
	public function delete(){
		$id = $this->_post('id');
		if ($id  > 0){
			$model = D('File');
			$data = $model->find($id);
			if($data['id']){
				if (is_file($data['file_path'])){
					unlink($data['file_path']);
				}
				if ($model->delete($id)){
					$this->success('删除成功');
				}
			}
		}
		$this->error('系统出错，操作失败');
	}
	
	/**
	 * 显示文件列表
	 */
	public function showlist(){
		$width = (int) $this->_config['width'];
		$height = (int) $this->_config['height'];
		$datas = getFiles($this->_config['value'], true, $width > 0 ? $width : 130, $height > 0 ? $height : 130, $this->_dbconfig);
		if($datas){
			if (self::$_firstTime === FALSE){
				self::$_firstTime = 1;
				$this->assign('first_time', 1);
			}else{
				self::$_firstTime ++;
				$this->assign('first_time', self::$_firstTime);
			}
			
			$this->assign('datas', $datas)
				->assign('image_exts', self::$IMAGE_ARR)
				->display();
		}
	}
	
	
	/**
	 * 获取文件信息，允许同时获取多个，用逗号隔开
	 */
	private function _getFile($file_id, $width=100, $height=100){
		if (empty($file_id)) return ;
		$where['id'] = array('in', $file_id);
		if ($where){
			$datas = $model = D('File')->where($where)->field('id,name,file_ext,file_path,root_path')->select();
			if ($datas){
				foreach ($datas as $key=>$vo){
					$datas[$key]['path'] = __ROOT__ . $vo['root_path'];
					if (in_array($vo['file_ext'], $this->_file_ext['img_ext']['ext_name'])){
						$datas[$key]['is_image'] = 1;
						$datas[$key]['pos'] = getPos($vo['file_path']);
					}else{
						$datas[$key]['is_image'] = 0;
					}
				}
			}
			return $datas;
		}
	}
	
}


function getPos($img, $width=100, $height=100){
	if (!is_file($img))  return false;
	$survey = getimagesize($img);
	//图像文件不存在
	if (false === $survey) return false;
	$top = $left = 0;
	if ($survey[0] <= $width && $survey[1] <= $height){
		$w = $survey[0];
		$h = $survey[1];
	}elseif ($survey[0] <= $width && $survey[1] > $height){
		$h = $height;
		$w = $survey[0] * ($height / $survey[1]);
	}elseif ($survey[0] > $width && $survey[1] <= $height){
		$w = $width;
		$h = $survey[1] * ($width / $survey[0]);
	}else{
		$h = $survey[1] * ($width / $survey[0]);
		if ($h <= $height){
			$w = $survey[0] >= $width ? $width : $survey[0];
		}else{
			$h = $survey[1] >= $height ? $height : $survey[1];
			$w = $survey[0] * ($height / $survey[1]);
		}
	}
	
	$top = ($height - $h + 1 - 1) / 2;
	$left = ($width - $w + 1 - 1) / 2;
	
	return array(
		'width' => (int)$w,
		'height' => (int)$h,
		'left' => (int)$left,
		'top' => (int)$top
	);
}
?>