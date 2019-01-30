<?php

/**
 * 上传模型
 */
class UploadModel extends Model
{

	public function upload($p_path='') {
		import("@.ORG.UploadFile");
		$path = 'Public/Forum/'.date('Ym').'/'.date('d').'/';
		$p_path = $p_path ? $p_path : ROOT . $path;
		@mkdirs($p_path);
		//导入上传类
		$upload						 = new UploadFile();
		//设置上传文件大小
		$upload->maxSize			 = 32922000;
		//设置上传文件类型
		$upload->allowExts			 = explode(',', 'jpg,gif,png,jpeg');
		//设置附件上传目录
		$upload->savePath			 = $p_path;
		//设置需要生成缩略图，仅对图像文件有效
		$upload->thumb				 = false;
		// 设置引用图片类库包路径
		$upload->imageClassPath		 = '@.ORG.Image';
		//设置需要生成缩略图的文件后缀
		$upload->thumbPrefix		 = 'm_,s_';  //生产2张缩略图
		//设置上传文件规则
		$upload->saveRule			 = 'uniqid';
		//删除原图
		$upload->thumbRemoveOrigin	 = true;
		if( !$upload->upload() ) {
			//捕获上传异常
			return false;
		} else {
			//取得成功上传的文件信息
			$uploadList		 = $upload->getUploadFileInfo();
			return $uploadList;
		}
	}

	/**
	 * 上传64位图片
	 * 
	 * @param string $p_path 保存路径
	 * @param string $p_image	BASE64字符串
	 */
	public function upload_by_base64($p_path, $p_image)
	{
		$image = base64_decode($p_image);
		$fileinfo = getimagesizefromstring($image);
		$fileType = strtolower(substr(image_type_to_extension($fileinfo[2]), 1));

		$file = array(
			'name' => time().'.'.$fileType,
			'savename' => time().rand(10000,99999).'.'.$fileType,
			'type' => $fileType,
			"width" => $fileinfo[0],
			"height" => $fileinfo[1],
		);
//		echo $p_path.$file['savename'];
		file_put_contents($p_path.$file['savename'], $image);
		return $file;
	}
}

?>