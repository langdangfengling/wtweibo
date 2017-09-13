<?php 
// +----------------------------------------------------------------------
// | Admin 基类控制器
// +----------------------------------------------------------------------
// | date:2017-05-16
// +----------------------------------------------------------------------
// | Author: lzb
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\BaseController;

class AdminBaseController extends BaseController{
	/**
	 * 初始化方法
	 */
	public function _initialize(){
		// 判断用户是否登录
        if(empty($_SESSION['admin'])){
        	$this->redirect('Common/login');
        }else{
        	$this->assign('admin', session('admin'));
        }
        //查询菜单权限
        $sidebar = D('AdminNav')->getTreeData('level','order_number DESC,id');
		$this->assign('sidebar', $sidebar);
	}
}
?>