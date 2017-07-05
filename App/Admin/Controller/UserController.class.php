<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/6 0006
 * Time: 21:34
 */

namespace Admin\Controller;

use Model\UserViewModel;
use Common\Org\Page;
use Think\Controller;
class UserController extends AdminBaseController
{
  /**
   * 用户列表
   */
    public function index(){
        $userView=new UserViewModel();
        $count=$userView->count();
        $p=I('get.p','','intval');
        $p=$p?$p:1;
        $limit=($p-1)*10;
        $limit=$limit.',10';
        $data=$userView->limit($limit)->select();
        $page=new Page($count,10);
        $this->assign('data',$data);
        $this->assign('page',$page->show());
        $this->display();
    }
    /**
     * 用户锁定，解锁
     */
    public function lock(){
        $type=I('get.type');
        $id=I('get.id');
        $msg=$type?'锁定用户':'解锁用户';
        if(M('user')->where(array('id' => $id))->setField('lock',$type)){
            $this->success($msg.'成功!',U('Admin/User/index'));
        }else{
            $this->error($msg.'失败!');
        }

    }
    /**
     * 用户检索
     */
    public function serchUser(){
         if(IS_GET && isset($_GET['submiting'])){
             $keyword=trim(I('get.keyword'));
             if($keyword) {
                 $userView = new UserViewModel();
                 $count = $userView->count();
                 $p=I('get.p','','intval');
                 $p=$p?$p:1;
                 $limit=($p-1)*10;
                 $limit=$limit.',10';
                 $data = $userView->where(array('username' => array('like', '%' . $keyword . '%')))->limit($limit)->select();
                 $page = new Page($count, 10);
                 $this->assign('keyword', $keyword);
                 $this->assign('page', $page->show());
             }
         }
        $this->assign('data', $data?$data:false);
        $this->display();
    }
}