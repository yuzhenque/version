<?php
/**
 * 
 * +------------------------------------------------------------+
 * @category Data
 * +------------------------------------------------------------+
 * 用于读取和创建前台所需缓存
 * +------------------------------------------------------------+
 *
 * @author Taylor <lixj@suncco.com>
 * @copyright http://www.suncco.com
 * @version 1.0
 */
class Data {
	/**
	 * 创建和读取企业信息缓存文件
	 * @staticvar boolean $data
	 * @param type $company_id 公司
	 * @param type $force 强制更新
	 */
	static public function company($company_id, $force = false){
		if ($company_id <= 0) return;
		static $data = false;
		if ($data === false){
			$condition['id'] = $company_id;
			$model = M();
			$data = $model->table(SAAS_TABLE_PREFIX.'company')->field('com_name,help_step')->where($condition)->find();
			unset($model);
		}
		return $data;
	}
	
	//读取或创建阶梯水价缓存
	static public function ladder($tariff_id=0, $force = false){
		static $data = false;
		$path = PUBLIC_TEMP_PATH . 'public/' . THE_COMPANY_ID. DS;
		$data = $force ? false : F('ladder', '', $path);
		if ($data === false){
			$data = M('ladder')->order('start_cube ASC')->groupBySelect('tariff_id', true, true);
			F('ladder', $data, $path);
		}
		
		return $tariff_id>0 ? (isset($data[$tariff_id]) ? $data[$tariff_id] : NULL) : $data;
	}
	
	//读取或创建系统参数配置缓存文件
	static public function system($company_id, $identy=null, $default=null, $force = false){
		static $data = false;
		if ($data === false){
			$data = M('system')->field('identy,attvalue')->where('company_id=%s', $company_id)->select();
		}
		
		return $identy ? (isset($data[$identy]) ? $data[$identy]['attvalue'] : $default) : $data;
	}
	
	//附加费用
	static public function surcharge($force = false){
		static $data = false;
		$data = $force ? false : F('surcharge', '', PUBLIC_TEMP_PATH . 'public/' . THE_COMPANY_ID. DS);
		if ($data === false){
			$data = self::_cache('surcharge', 'surcharge', '*' ,'', '', '', '', false, PUBLIC_TEMP_PATH . 'public/');
		}
		if ($data){
			foreach ($data as $vo){
				$f_data[$vo['id']] = $vo;
			}
			unset($data);
		}
		return empty($f_data) ? array() : $f_data;
	}
	
	//水表口径
	static public function caliber($id = 0, $force = false){
		static $data = false;
		$data = $force ? false : F('caliber', '', PUBLIC_TEMP_PATH . 'public/' . THE_COMPANY_ID. DS);
		if ($data === false){
			$data = self::_cache('caliber', 'caliber', '*' ,'id', '', '', '', false, PUBLIC_TEMP_PATH . 'public/');
		}
		
		return empty($data) ? array() : ($id>0 ? $data[$id]['caliber_size'] : $data);
	}
	
	//读取或创建用户状态信息缓存文件
	static public function status($id = 0, $force = false){
		static $data = false;
		$data = $force ? false : F('status', '', PUBLIC_TEMP_PATH . 'public/' . THE_COMPANY_ID. DS);
		if ($data === false){
			$datas = M('status')->field('id, name, is_allow')->select();
			$data = array();
			if ($datas){
				$idx = 1;
				foreach ($datas as $v){
					$v['index'] = $idx;
					$idx ++ ; 
					$data[$v['id']] = $v;
				}
			}
			unset($datas);
			F('status', $data, PUBLIC_TEMP_PATH . 'public/' . THE_COMPANY_ID. DS);
		}
		
		return empty($data) ? array() : ($id>0 ? $data[$id] : $data);
	}
	
	//读取或创建水价类别信息缓存文件
	static public function tariff($id = 0, $force = false){
		static $data = false;
		$data = $force ? false : F('tariff', '', PUBLIC_TEMP_PATH . 'public/' . THE_COMPANY_ID. DS);
		if ($data === false){
			$data = self::_cache('tariff', 'tariff', '*' ,'id', '', '', '', false, PUBLIC_TEMP_PATH . 'public/');
		}
		
		return empty($data) ? array() : ($id>0 ? $data[$id] : $data);
	}
	
	//读取或创建区域信息缓存文件
	static public function region($force = false){
		static $data = false;
		$data = $force ? false : F('region', '', PUBLIC_TEMP_PATH . 'public/' . THE_COMPANY_ID. DS);
		if ($data === false){
			$data = self::_cache('region', 'region', '*' ,'id', '', 'userlist', '', false, PUBLIC_TEMP_PATH . 'public/');
		}
		
		return empty($data) ? array() : $data;
	}
	
	//批量创建按抄表员分组生成对应负责区域ID的缓存文件
	static public function betchRegionByAdmin(){
		if (is_dir(PUBLIC_TEMP_PATH . 'public/' . THE_COMPANY_ID. '/region/')){
			$files = recurdir(PUBLIC_TEMP_PATH . 'public/' . THE_COMPANY_ID. '/region/');
			if (is_array($files)){
				foreach ($files as $file){
					@unlink($file['dir'] . '/' . $file['name']);
				}
			}
		}
		$datas = M('admin_region')->distinct('admin_id')->field('admin_id')->select();
		foreach ($datas as $vo){
			self::regionByAdmin($vo['admin_id'], true);
		}
	}
	
	//读取和创建按抄表员分组生成对应负责区域ID的缓存文件
	static public function regionByAdmin($admin_id, $force = false){
		static $data = false;
		$path = PUBLIC_TEMP_PATH . 'public/' . THE_COMPANY_ID. '/region/';
		$data = $force ? false : F($admin_id, '', $path);
		
		if ($data === false){
			$condition['admin_id'] = $admin_id;
			$datas = M('admin_region')->where($condition)->field('region_id')->select();
			$data = array();
			if (is_array($datas)){
				foreach ($datas as $vo){
					$data = extend($data, getRecRegion($vo['region_id']));
				}
			}
			
			unset($datas);
			F($admin_id, array_unique($data?$data:array()), $path);
		}
		
		return $data;
	}
	
	//创建角色缓存文件
	static public function group($group_id=0, $force = false){
		static $data = false;
		$data = $force ? false : F('group', '', TEMP_PATH . THE_COMPANY_ID. DS);
		if ($data === false){
			$data = self::_cache('group', 'group', '*' ,'id', '', 'permissions', '', false, TEMP_PATH);
		}
		
		return empty($data) ? array() : ($group_id>0 ? $data[$group_id] : $data);
	}
	
	
	/**
	 * 
	 * +------------------------------------------------------------+
	 * @name create_cache
	 * +------------------------------------------------------------+
	 * 通用创建公共临时缓存文件
	 * +------------------------------------------------------------+
	 *
	 * @author anzai <sba3198178@126.com>
	 * @version 1.0
	 * 
	 * @example
	 *
	 * @param string $filename 缓存文件名
	 * @param string $table 所查询的表名
	 * @param string $column 查询的字段
	 * @param string $field 指定一个字段的值作为键值，对数据重新索引
	 * @param mixed $where 查询组合条件
	 * @param string $serialize 需要进行反序列表的字段信息，多个逗号隔开
	 * @param string $order 需要进行排序的字段，默认升序
	 * @param boolean $dir 排序方式，true升序  false降序
	 * @param string $tmpDir 临时文件存放目录
	 *
	 */
	static private function _cache($filename, $table, $column='*' ,$field='', $where=null, $serialize='', $order='', $dir=true, $tmpDir = ''){
		$model = M($table);
		$model->field($column);
		
		if ($where){
			$model->where($where);
		}
		if ($order){
			$model->order($order . ($dir ? ' ASC' : ' DESC'));
		}
		$serialize = empty($serialize) ? array() : explode(',', $serialize);
		
		$datas = $model->select();
		
		if (!empty($field) || !empty($serialize)){
			$tmp = array();
			foreach ($datas as $vo){
				foreach ($serialize as $k){
					$unsdata = unserialize($vo[$k]);
					$vo[$k] = is_array($unsdata) ? $unsdata : $vo[$k];
					unset($unsdata);
				}
				if ($field && isset($vo[$field])){
					$tmp[$vo[$field]] = $vo;
				}else{
					$tmp[] = $vo;
				}
			}
			
			$datas = $tmp;
			unset($tmp);
		}
		
		F($filename, $datas, ($tmpDir ? $tmpDir : PUBLIC_TEMP_PATH . 'public/') . THE_COMPANY_ID . DS);
		
		return $datas;
	}
}