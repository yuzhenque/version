<?php

/**
 * ------------------------------------
 * 附件管理
 *
 * @author Bluefoot. 2013-11-26
 * ------------------------------------
 */
class AttachmentWidget extends CommonAction
{
	
	/**
	 * 获取附件上传模块
	 * 
	 * @param string $p_title			标题
	 * @param string $p_from			来自
	 * @param string $p_defaultId		图片DOM ID，确定后生成attachmemnt_(+$p_domId)的Input组
	 * @param string $p_defaultImage	默认图片
	 * @param string $p_type			上传文件类型[image,flash,media,file]
	 * @param int $p_autoFlush			上传完，确定后上一级是否刷新[默认0否，1是]
	 * @param int $p_multi				是否可以上传多个[默认0否，1是]
	 */
	public function upload($p_title, $p_from, $p_defaultId, $p_defaultImage, $p_type='image', $p_autoFlush=0, $p_multi=0)
	{
		$this->assign('btnTitle',	$p_title);
		$this->assign('defaultId',		$p_defaultId);
		$this->assign('defaultImage',	$p_defaultImage);
		$this->assign('from',		$p_from);
		$this->assign('fileType',	$p_type);
		$this->assign('autoFlush',	$p_autoFlush);
		$this->assign('multi',		$p_multi);
		$this->display('Attachment:wd_upload');
	}

}

?>