<?php
/**
 +----------------------------------------------------------------------------
 * 缩略图
 * 
 +----------------------------------------------------------------------------
 */
class ThumbModel extends Action{
	
	/**
	 * 截取文件，并返回截取后的路径
	 * 
	 * @param type $p_file
	 * @param type $p_width
	 * @param type $p_height
	 */
	public function create($p_file, $p_width, $p_height, $p_output=0, $p_default=1){
		
		$path = ROOT. '/';
		$image_file = $p_file;
		
		if(!empty($p_file) && is_numeric($p_file)){
			$image_file = D('File')->getFieldById($p_file, 'root_path');
		}
		
		$trueImage = str_replace('//', '/', $path . $image_file);
		
		if(empty($image_file) || !file_exists($trueImage) || is_dir($trueImage)){
			if($p_default == 1){
                $image_file = '/Uploads/Default/error.png';
            }else{
                $image_file = '/Uploads/Default/error_'. $p_default .'.png';
            }
			if($p_width == 0 || $p_height == 0){
				return $image_file;
			}else{
				$thumb_image_file = 'Uploads/Default/error_'. $p_default .'_'.$p_width.'x'.$p_height.'.png';
			}
		}elseif($p_width == 0 && $p_height == 0){
			return $image_file;
		}else{
			$imageArr = explode('.', $image_file);
			$thumb_image_file = $imageArr[0].'/'.$p_width.'x'.$p_height .'.'.$imageArr[1];
		}
		
		$thumb_image_file = str_replace('//', '/', 'Tmp/'. $thumb_image_file);
		
		$trueImage = str_replace('//', '/', $path . $image_file);
		$trueTbImage = str_replace('//', '/', $path . $thumb_image_file);
		$trueTbImage = str_replace('//', '/', $trueTbImage);
		
		// 如果缩略图对应目录已经存在图片文件，直接读取输出
		if(file_exists($trueTbImage)){
			if($p_output == 1){
				$image_info = $this->getImageInfo($trueTbImage);
				$createFun = str_replace('/', 'createfrom', $image_info['mime']);
				$im = $createFun($trueTbImage);
				$this->output($im, $image_info['type']);
			}
			return $thumb_image_file;
		}
		
		// 不存在则新建目录
		if(!is_dir(dirname($trueTbImage))){
			@mkdirs(dirname($trueTbImage), 0777, true);
		}
		
		//dump(dirname($trueTbImage));exit;
		
		$image_info = $this->getImageInfo($trueImage);
		
		$createFun = str_replace('/', 'createfrom', $image_info['mime']);
        $im = $createFun($trueImage);
		
		// 根据填充模式 fill，计算原图的截图宽和高
		$crop_resize = $this->croptofill($image_info['width'], $image_info['height'], $p_width, $p_height);
		
		// 裁剪坐标范围，以(0,0)为起点
		$coords = array(
			'x' => 0,
			'y' => 0,
			'w' => $crop_resize['crop_width'],
			'h' => $crop_resize['crop_height'],
		);
		
		$crop_im = $this->crop($im, $coords, $crop_resize['width'], $crop_resize['height']);
		
		// 写入文件
		$this->output($crop_im, $image_info['type'], $trueTbImage);
		if($p_output == 1){
			$image_info = $this->getImageInfo($trueTbImage);
			//var_dump($trueTbImage);exit();
			$createFun = str_replace('/', 'createfrom', $image_info['mime']);
			$im = $createFun($trueTbImage);
			$this->output($im, $image_info['type']);
		}
		
		return $thumb_image_file;
	}

	
	/**
	 * 检测缩略图片尺寸是否允许
	 * @param int $width 宽
	 * @param int $height 高
	 * @reutrn boolean 
	 */
	private function checkSize($width, $height){
		$in_allow = false;
		if($this->_allow_sizes){
			foreach($this->_allow_sizes as $allow_size){
				list($allow_width, $allow_height) = explode('*', $allow_size);
				if($width == $allow_width && $height == $allow_height){
					$in_allow = true;
					break;
				}
			}
		}
		return $in_allow;
	}
	
	
	/**
	 * 获取图片信息
	 */
	private function getImageInfo($image_file){
		$imageInfo = getimagesize($image_file);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($image_file);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            return $info;
        } else {
            return false;
        }
	}
	
	/**
	 * 计算截取的宽和高以适应fill填充模式
	 * 
	 * @param int $image_width	原图的宽
	 * @param int $image_height	原图的高
	 * @param int $show_width	要显示的宽度
	 * @param int $show_height	要显示的高度
	 * 
	 * @reutrn array (width, height)  返回计算裁剪后的宽和高
	 */
	private function croptofill($image_width, $image_height, $show_width, $show_height){
		
		//检查有一部分是0的情况
		if($show_width == 0 && $show_height == 0){
			$show_width = $image_width;
			$show_height = $image_height;
		}elseif($show_width == 0){
			$show_width = ($show_height/$image_height) * $image_width;
		}elseif($show_height == 0){
			$show_height = ($show_width/$image_width) * $image_height;
		}
		
		// 原图：以宽为准
		$crop_width 	= $image_width;
		// 则截取的高应该为：
		$crop_height 	= ($crop_width / $show_width) * $show_height;
		
		//将要截取的高大于实际高,则需要以高为准,截宽
		if($image_height < $crop_height){
			$crop_height 	= $image_height;
			$crop_width 	= $crop_height * $show_width / $show_height;
		}
		
		$result = array(
			'width' 	=> $show_width,
			'height' 	=> $show_height,
			'crop_width' => $crop_width,
			'crop_height' => $crop_height
		);
		return $result;
	}
	
	
	/**
	 * 控制浏览器缓存
	 * @param $interval
	 * @param $type
	 */
	private function httpCacheControl($interval, $type='public'){
		if('nocache' == $type){
			header('Expires: -1');
            header('Pragma: no-cache');
            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        }elseif ('public' == $type){
        	$lastmodified = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
        	if(isset($lastmodified) && (strtotime($lastmodified) + $interval) > time()){
        		// 未过期，发送304
                header("Expires: " . gmdate("D, d M Y H:i:s", (strtotime($lastmodified) + $interval)) . " GMT");
                header("Cache-Control: public,max-age=" . $interval);
                header("Last-Modified: " . gmdate("D, d M Y H:i:s", strtotime($lastmodified)) . " GMT");
                header('HTTP/1.1 304 Not Modified');
            }else{
                header("Expires: " . gmdate("D, d M Y H:i:s", time() + $interval) . " GMT");
                header("Cache-Control: public,max-age=" . $interval);
                header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()). " GMT");
            }
        }
	}
	
	
	/**
	 * 裁剪图片
	 */
	private function crop($srcImg, $coords, $cropWidth, $cropHeight){
		
		$cropImg = imagecreatetruecolor( $cropWidth, $cropHeight);
		
		//计算比例
		$rx = $cropWidth / $coords['w'];
		$ry = $cropHeight / $coords['h'];
		
		//取得缩放后的宽和高
		$zoomWidth = abs((int)Math.round($rx * imagesx($srcImg)));
		$zoomHeight = abs((int)Math.round($ry * imagesy($srcImg)));
			
		//取得缩放的图片
		$zoomImg = imagecreatetruecolor($zoomWidth, $zoomHeight);
		imagecopyresampled($zoomImg,$srcImg,0,0,0,0,$zoomWidth, $zoomHeight, imagesx($srcImg), imagesy($srcImg));
			
		//取得缩放图片的相对位置
		$cropX = abs((int)Math.round($rx * $coords['x']));
		$cropY = abs((int)Math.round($ry * $coords['y']));
			
		imagecopyresampled($cropImg, $zoomImg, 0, 0, abs(($zoomWidth-$cropWidth)/2), abs(($zoomHeight-$cropHeight)/2), $cropWidth, $cropHeight, $cropWidth, $cropHeight);
		return $cropImg;
	}
	
	
	/**
	 * 输出图片
	 */
	private function output($im, $type='png', $filename='', $quality=100){
        $ImageFun='image'.$type;
        
		if(empty($filename)) {
	        header("Content-type: image/".$type);
	        $ImageFun($im);
		}else{
			if($type == 'png'){
		        $ImageFun($im, $filename);
			}else{
		        $ImageFun($im, $filename, $quality);
			}
		}
        imagedestroy($im);
    }
	
	/**
	 * 生成用户推广链接二维码
	 */
	public function create_promote_qrcode($p_user_id)
	{
		if(empty($p_user_id)){
			$p_user_id = 1;
		}
		import("Public.Class.Qrcode.phpqrcode", "/", ".php");
		$errorCorrectionLevel = "M";  
		$matrixPointSize = "9";
		
		$url = U('Index/promote', 'promote='.$p_user_id, true, false, true);
		
		$path = "/Public/UserQrcode/". substr($p_user_id, -1) .'/';
		$filepath = ROOT . $path;
		@mkdirs($filepath);
		$fileName = $filepath . $p_user_id . '.png';
		
		if(!file_exists($fileName)){
			QRcode::png($url, $fileName, $errorCorrectionLevel, $matrixPointSize, false);
		}
		return $path. $p_user_id . '.png';
	}
	
}
?>