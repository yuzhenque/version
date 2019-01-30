<?php

/**
 * 日志管理
 * 
 * @author bluefoot<bluefoot@qq.com>
 * @version 2014-06-04
 */
class AdminLogAction extends CommonAction
{

	function index()
	{
		$this->addStep('日志管理');
		$page	 = $_GET['p'] ? isNum($_GET['p']) : 1;
		$Model	 = D("AdminLog");
		$keyword = htmlspecialchars($_GET['keyword']);
		$wsql	 = $keyword ? "username='$keyword'" : '';
		$count	 = $Model->where($wsql)->count();
		import("ORG.Util.Page"); //导入分页类
		$p		 = new Page($count, Conf('PAGE_NUM'));
		$list	 = $Model->where($wsql)->limit($p->firstRow . ',' . $p->listRows)->order('id desc')->select();
		$this->assign('list', $list);
		$this->assign('page', $p->show());
		$this->assign('keyword', $keyword);
		$this->display();
	}

	function del()
	{
		$id		 = $_GET['id'] ? isNum($_GET['id']) : '';
		$ids	 = $_POST['ids'] ? $_POST['ids'] : '';
		$Model	 = D("AdminLog");
		if($id){
			$isdel = $Model->where("id='$id'")->delete();
			if($isdel == true){
				$this->success('删除成功!');
			}
			else{
				$this->error('删除失败!');
			}
		}
		elseif($ids){
			foreach($ids as $id){
				$isdel[$id] = $Model->where("id='$id'")->delete();
			}
			$this->success('批量删除成功!');
		}
		else{
			$this->error('操作失败!');
		}
	}

	function clear()
	{
		$Model	 = D("Log");
		$isdel	 = $Model->execute("TRUNCATE TABLE `log`");
		$this->success('清空数据成功!');
	}

}

?>