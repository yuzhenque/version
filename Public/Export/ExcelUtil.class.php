<?php
class ExcelUtil{
	public $_filename;//导出文件名
	public $_data_title = array();

	public function __construct() {
        
    }
    /**
	 * 导入数据，将Excel文件转换成数组
	 * 
	 * @param string	$excel_file  	xls文件
	 * @param boolean	$remove_title	是否去掉标题
	 * @return mixed $sheet
	 */
	public static function import($excel_file, $remove_title=true){
		
		//载入PHPExcel入口文件
		import("PHPExcel",'/Public/Class/PHPExcel/');
		
		$reader = new PHPExcel_Reader_Excel2007();
		if(!$reader->canRead($excel_file)){
			$reader = new PHPExcel_Reader_Excel5();
			if(!$reader->canRead($excel_file))
				$this->error('对不起，该Excel文件无法读取！');
		}
		$excel = $reader->load($excel_file);
		$sheet = $excel->getSheet()->toArray();
		
		if($remove_title){
			array_shift($sheet); // 去掉标题说明
		}
		
		return $sheet;
	}

    /**
	 * 导出xls文件，将数组转换成Excel文件
	 * 
	 * @param array 	$header_map	导出文件Excel标题HashMap，对应字段名=>别名
	 * @param array 	$list 		待导出的数据列表
	 * @param string	$filename	导出的文件名
	 * 	
	 */
	public static function export($header_map, $list, $filename='', $download=true){
		
		$filename = empty($filename) ? time() : $filename;
		if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows nt') == true){
			$filename = iconv('utf-8', 'gb2312', $filename);
		}
			
		import("PHPExcel",'/Public/Class/PHPExcel/');
		
		$phpExcel 	= new PHPExcel();
		$writer 	= new PHPExcel_Writer_Excel5($phpExcel);
		$phpExcel->setActiveSheetIndex(0);
		$sheet 		= $phpExcel->getActiveSheet();
		$sheet->setTitle('sheet1');
			
		$i = $chr = ord('A');
		$ls = ord('Z');
		$pre = '';

		foreach($header_map as $vo){
			if ($chr > $ls){
				$chr = ord('A');
				$pre = chr($chr+floor($i/$ls)-1);       //旧代码使用 ceil 是错的，改成  floor
			}
			$sheet->setCellValue($pre.chr($chr).'1', is_array($vo) ? $vo['title']: $vo);
			//设置字体居中
			$sheet->getStyle($pre.chr($chr).'1')->getAlignment()->setHorizontal('center');
			$chr++;
			$i++;
		}

			
		if ($list && is_array($list)){
			$code = 2;
			foreach ($list as $vo){
				$j = $chr = ord('A');
				$pre = '';
				
				foreach($header_map as $key => $v){
					if ($chr > $ls){
						$chr = ord('A');
						$pre = chr($chr+floor($j/$ls)-1);       //旧代码使用 ceil 是错的，改成  floor
					}
					$value = isset($vo[$key]) ? $vo[$key] : '';

					$sheet->setCellValue($pre.chr($chr).$code, $value);
					$format = is_numeric($value)?'0':'@';
					$sheet->getStyle($pre.chr($chr).$code)->getNumberFormat()->setFormatCode($format);
					$chr++;
					$j++;
				}
				
				$code++;
			}
		}
		unset($list);
			
		//下载Excel表格
		if ($download){
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header('Content-Disposition:inline;filename="'.$filename.'.xls"');
			header("Content-Transfer-Encoding: binary");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Pragma: no-cache");
			$writer->save('php://output');
			exit;
		}else{
			//保存表格
			$writer->save($filename.'.xls');
			return iconv('gb2312', 'utf-8', $filename).'.xls';
		}
	}
}
?>