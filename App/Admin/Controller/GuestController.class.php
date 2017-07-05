<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/6 0006
 * Time: 21:34
 */

namespace Admin\Controller;

use Common\Org\Page;
use Model\GuestViewModel;
use Model\ReplyViewModel;
use Think\Controller;
class GuestController extends AdminBaseController
{
  public function index(){
      if(IS_GET && isset($_GET['submiting'])){
          $keyword = trim(I('get.keyword'));
          if (isset($keyword) && $keyword) {
              $where = array('content' => array('like', '%' . $keyword . '%'));
          }
      }
      $p=I('get.p','','intval');
      $p=$p?$p:1;
      $limit=($p-1)*10;
      $where=$where?$where:'';
      $guestView=new GuestViewModel();
      $data=$guestView->where($where)->limit($limit.',10')->select();
      $count=$guestView->where($where)->count();
      if(!empty($data) && is_array($data)){
          foreach($data as $k => $v){
              $data[$k]['username'] =M('userinfo')->where(array('uid' => $v['uid']))->getField('username');
          }
      }
//      p($data);
      $page=new Page($count,10);
      $this->assign('keyword',$keyword);
      $this->assign('count',$count);
      $this->assign('page',$page->show());
      $this->assign('data',$data);
      $this->display();
  }

    public function delGuest(){
         if(IS_GET){
             $id=I('get.id','','intval');
             if(M('guest')->delete($id)){
                 //同时删除该留言下的所有回复
                 $rids=M('reply')->where(array('gid' => $id))->delete();
                 p($rids);
                 $this->success('删除留言成功',U('Admin/Guest/index'));
             }else{
                 $this->error('删除留言失败');
             }

         }
    }

    public function reply(){
        $replyView=new ReplyViewModel();
        if(IS_GET && isset($_GET['submiting'])){
            $keyword = trim(I('get.keyword'));
            if (isset($keyword) && $keyword) {
                $where = array('content' => array('like', '%' . $keyword . '%'));
            }
        }
        $p=I('get.p','','intval');
        $p=$p?$p:1;
        $limit=($p-1)*10;
        $where=$where?$where:'';
        $data= $replyView->where($where)->limit($limit.',10')->select();
        $count= $replyView->where($where)->count();
//      p($data);
        $page=new Page($count,10);
        $this->assign('keyword',$keyword);
        $this->assign('count',$count);
        $this->assign('page',$page->show());
        $this->assign('data',$data);
        $this->display();
    }
    public function delReply(){
        if(IS_GET){
            $id=I('get.id','','intval');
            if(M('reply')->delete($id)){
                $this->success('删除回复成功',U('Admin/Guest/reply'));
            }else{
                $this->error('删除回复失败');
            }

        }
    }
}