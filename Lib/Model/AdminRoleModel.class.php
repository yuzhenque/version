<?php

class AdminRoleModel extends Model
{

	// 自动验证设置
	protected $_validate = array(
		array('title', 'require', '角色名称不能为空！'),
	);
	// 自动填充设置
	protected $_auto	 = array(
		array('resources', 'getAuths', 3, 'callback'),
	);

	/**
	 * 权限序列化
	 */
	public function getAuths() {
		$_auths	 = $_POST['permissions'];
		$str	 = json_encode($_auths);
		return $str;
	}

	protected function _after_find(&$result, $options) {
		if(!empty($result['resources'])){
			$result['resources'] = json_decode($result['resources']);
			$result['resources'][] = 'index-index';
		}
	}
	
	/**
	 * 获取角色信息
	 * @param type $id
	 * @return type
	 */
	public function getRole($id) {
		$data = $this->where("id='$id'")->find();
		return $data;
	}

	/**
	 * 获取顶部权限
	 * @param type $RoleID
	 * @return type
	 */
	public function getRoleMenu($RoleID, $p_from='admin') {
		$menu		 = C("MENU");
		$topMenu	 = array();
		$childMenu	 = array();
		$curMenu	 = array();
		$resources	 = array();
		$resourcesMenu	 = array();

		if($p_from == 'pad'){
			$RoleID = 1;
		}
		
		$group['status'] = 100;
		
		if(!empty($group['resources'])){
			foreach($group['resources'] as $k=>$v){
				$group['resources'][$k] = strtolower($v);
			}
		}
		
		$resourcesMenu = $menu;
		foreach ($menu as $m => $val) {
			if ( empty($val['m']) ) {
				$val['m'] = $m;
			}
			if ( empty($val['a']) ) {
				$val['a'] = 'index';
			}
			if ( !empty($val['hide']) ) {
				unset($menu[$m], $resourcesMenu[$m]);
				continue;
			}
			
			//获得二级三级
			if (!empty($val['list']) ) {
				$check = false;
				foreach ($val['list'] as $ak => $av) {
					if ( empty($av['list']) ) {
						continue;
					}
					if ( empty($av['m']) ) {
						$av['m'] = $ak;
					}
					if ( empty($av['a']) ) {
						$av['a'] = 'index';
					}
					$menu[$m]['list'][$ak]['url'] = U($av['m'] . '/' . $av['a'], 'ch='. (isset($av['ch']) ? $av['ch'] : ''));
					
					
			
					//如果是内容管理，则提取栏目
					if(!empty($av['is_server'])){
						$channel = C('server_type');
						$list = array();
						if(!empty($channel)){
							foreach($channel as $channelk=>$channelv){
								$tmp = array(
									'title' => $channelv,
									'm'		=> 'server',
									'a'		=> 'index',
									'ch'	=> $channelk,
									'list'	=> array()
								);
								$list[] = $tmp;
							}
						}
						if(!empty($list)){
							$menu[$m]['list'][$ak]['list'] = $av['list'] = array_merge($list, $av['list']);
						}
					}

					$menu[$m]['list'][$ak]['url'] = U($av['m'] . '/' . $av['a'], 'ch='. (isset($av['ch']) ? $av['ch'] : ''));
					
					foreach ($av['list'] as $bk => $bv) {
						if ( empty($bv['m']) ) {
							$bv['m'] = $ak;
						}
						if ( empty($bv['a']) ) {
							$bv['a'] = 'index';
						}
						
						$resources[strtolower($bv['m'].'-'.$bv['a'])] = array($bv['title'], 1);
//						dump(strtolower($bv['m'].'-'.$bv['a']));
						if($group['status'] != 100 && !in_array(strtolower($bv['m'].'-'.$bv['a']), $group['resources'])){
							unset($menu[$m]['list'][$ak]['list'][$bk], $resourcesMenu[$m]['list'][$ak]['list'][$bk]);
							$resources[strtolower($bv['m'].'-'.$bv['a'])][1] = 0;
							continue;
						}
						
						$menu[$m]['list'][$ak]['list'][$bk]['url'] = U($bv['m'] . '/' . $bv['a'], 'ch='. (isset($bv['ch']) ? $bv['ch'] : ''));
						
//						echo MODULE_NAME.'='.$bv['m'].'<br/>';
//						if(MODULE_NAME == $bv['m']){
//							echo 'asdf';exit();
//						}
						
						if ( MODULE_NAME == $bv['m'] && ACTION_NAME == $bv['a'] && (empty($bv['ch']) || (!empty($bv['ch']) && $bv['ch'] == $_GET['ch'])) ) {
							$menu[$m]['current']							 = true;
							$menu[$m]['list'][$ak]['current']				 = true;
							$menu[$m]['list'][$ak]['list'][$bk]['current']	 = true;
							$curMenu = $menu[$m]['list'][$ak]['list'][$bk];
							$check = true;
						}elseif (!empty($bv['other'])) {
							foreach ($bv['other'] as $cv) {
								if ( MODULE_NAME == $bv['m'] && ACTION_NAME == $cv && (empty($bv['ch']) || (!empty($bv['ch']) && $bv['ch'] == $_GET['ch'])) ) {
									$menu[$m]['current']							 = true;
									$menu[$m]['list'][$ak]['current']				 = true;
									$menu[$m]['list'][$ak]['list'][$bk]['current']	 = true;
									$curMenu = $menu[$m]['list'][$ak]['list'][$bk];
									$check = true;
								}
							}
						}

						if ( !empty($bv['hide']) ) {
							unset($menu[$m]['list'][$ak]['list'][$bk]);
							continue;
						}
					}
					if(empty($resourcesMenu[$m]['list'][$ak]['list'])){
						unset($resourcesMenu[$m]['list'][$ak]);
					}
					if(empty($menu[$m]['list'][$ak]['list'])){
						unset($menu[$m]['list'][$ak]);
						continue;
					}
					$menu[$m]['list'][$ak]['url'] = $this->_get_fisrt($menu[$m]['list'][$ak]['list']);
				}
				if(empty($menu[$m]['list'])){
					unset($menu[$m]);
					continue;
				}
				if(empty($resourcesMenu[$m]['list'])){
					unset($resourcesMenu[$m]);
					continue;
				}
				//一级目录
				$menu[$m]['url'] = $this->_get_fisrt($menu[$m]['list']);
				$topMenu[$m] = $menu[$m];
				//二级
				if ( $check == true ) {
					$childMenu = $menu[$m]['list'];
				}
			}
		}
		return array(
			'topMenu'	 => $topMenu,
			'childMenu'	 => $childMenu,
			'curMenu'	 => $curMenu,
			'resources'	 => $resources
		);
	}

	private function _get_fisrt($p_data)
	{
		foreach($p_data as $val){
			return $val['url'];
		}
	}
}

?>