<?php

/**
 * 菜单设置
 */
$g_menuCfg['MENU'] = array(
	/*'Index' => array(
		'title'	 => '首页',
		'list'	 => array(
			'Index'	 => array(
				'title'	 => '系统',
				'list'	 => array(
					array('title' => 'ERP更新', 'a' => 'index', 'm' => 'ErpVersion', 'other'=>array('add', 'edit')),
					array('title' => '新增ERP版本', 'a' => 'add', 'm' => 'ErpVersion', 'hide' => true),
					array('title' => '编辑ERP版本', 'a' => 'edit', 'm' => 'ErpVersion', 'hide' => true),
					array('title' => '删除ERP版本', 'a' => 'delete', 'm' => 'ErpVersion', 'hide' => true),
					array('title' => '官网更新', 'a' => 'index', 'm' => 'WwwVersion', 'other'=>array('add', 'edit')),
					array('title' => '新增官网版本', 'a' => 'add', 'm' => 'WwwVersion', 'hide' => true),
					array('title' => '编辑官网版本', 'a' => 'edit', 'm' => 'WwwVersion', 'hide' => true),
					array('title' => '删除官网版本', 'a' => 'delete', 'm' => 'WwwVersion', 'hide' => true),
					array('title' => 'CLOUD更新', 'a' => 'index', 'm' => 'CloudVersion', 'other'=>array('add', 'edit')),
					array('title' => '新增CLOUD版本', 'a' => 'add', 'm' => 'CloudVersion', 'hide' => true),
					array('title' => '编辑CLOUD版本', 'a' => 'edit', 'm' => 'CloudVersion', 'hide' => true),
					array('title' => '删除CLOUD版本', 'a' => 'delete', 'm' => 'CloudVersion', 'hide' => true),
				),
			),
			'User'	 => array(
				'title'	 => '资料修改',
				'list'	 => array(
					array('title' => '密码修改', 'a' => 'editpass', 'm' => 'AdminUser'),
				),
			),
		)
	),*/
	'Sync' => array(
		'title'	 => '同步',
		'list'	 => array(
			'Sync'	 => array(
				'title'	 => '同步管理',
				'list'	 => array(
					array('title' => '同步操作', 'a' => 'index', 'm' => 'Sync'),
					array('title' => '脚本运行', 'a' => 'script', 'm' => 'Sync'),
//					array('title' => '日志', 'a' => 'log', 'm' => 'Sync'),
				),
			),
			'Server'	 => array(
				'title'	 => '服务器管理',
				'is_server' => false,
				'list'	 => array(
					array('title' => '服务器管理', 'a' => 'index', 'm' => 'Server', 'other'=>array('add', 'edit')),
					array('title' => '新增服务器', 'a' => 'add', 'm' => 'Server', 'hide' => true),
					array('title' => '编辑服务器', 'a' => 'edit', 'm' => 'Server', 'hide' => true),
				),
			),
		)
	),
	'System' => array(
		'title'	 => '系统设置',
		'list'	 => array(
			'System'	 => array(
				'title'	 => '系统设置',
				'list'	 => array(
					array(
						'title'	 => '基本配置',
						'a'		 => 'index',
					),
				)
			),
			
			'AdminUser'	 => array(
				'title'	 => '管理员设置',
				'list'	 => array(
					array(
						'title'	 => '管理员列表',
						'm'		 => 'AdminUser',
						'a'		 => 'index',
						'other'	 => array('add', 'edit')
					),
					array(
						'title'	 => '添加管理员',
						'm'		 => 'AdminUser',
						'a'		 => 'add',
						'hide'	 => true,
					),
					array(
						'title'	 => '修改管理员',
						'm'		 => 'AdminUser',
						'a'		 => 'edit',
						'hide'	 => true,
					),
					array(
						'title'	 => '管理组列表',
						'm'		 => 'AdminRole',
						'a'		 => 'index',
						'other'	 => array('add', 'edit')
					),
					array(
						'title'	 => '添加管理组',
						'm'		 => 'AdminUser',
						'a'		 => 'add',
						'hide'	 => true,
					),
					array(
						'title'	 => '修改管理组',
						'm'		 => 'AdminRole',
						'a'		 => 'edit',
						'hide'	 => true,
					),
				)
			)
		)
	),
);
?>