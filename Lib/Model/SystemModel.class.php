<?php

class SystemModel extends Model {
	
	/**
	 * 获取设置
	 */
	public function value($p_user_id, $p_key)
	{
		$tmp = $this->where("user_id='{$p_user_id}' AND identy='{$p_key}'")->find();
		if(!empty($tmp)){
			return $tmp['attvalue'];
		}
		return '';
	}
	
	/**
	 * 保存配置
	 * 
	 * @param type $p_user_id	用户ID
	 * @param type $type		类型
	 * @return boolean
	 */
	public function submitdata($p_user_id, $type = 'base'){
		$config = $_POST['config'];
		$condition = array();
		$condition['user_id']	 = $p_user_id;
		$condition['type']		 = $type;

		if (empty($config)) {
			$res = $this->where($condition)->delete();
			if (false !== $res){
				return true;
			}
			return false;
		}

		foreach ($config as $identy => $value){
			if (empty($identy)) continue;
			
			$condition['identy']	 = $identy;
			
			$set['attvalue'] = $value;
			
			//如果对应值存在，则更新；不存在，则插入
			$mcount = $this->where($condition)->count();
			if ($mcount > 0){
				D('System')->where($condition)->save($set);
			}else{
				$set['user_id'] = $p_user_id;
				$set['identy'] 	= $identy;
				$set['type'] 	= $type;
				D('System')->add($set);
			}
		}
		return true;
	}
	
	public function getValue($p_user_id, $p_type, $p_identy){
		$datas = $this->where("user_id='{$p_user_id}' AND type='{$p_type}'")->select();
		$tmp = array();
		if ($datas) {
			foreach ($datas as $vo){
				$tmp[$vo['identy']] = $vo['attvalue'];
			}
		}
		return $p_identy ? $tmp[$p_identy] : $tmp;
	}
}

?>