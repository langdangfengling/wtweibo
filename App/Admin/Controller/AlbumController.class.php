<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/18 0018
 * Time: 9:53
 */

namespace Admin\Controller;

use Model\AlbumModel;
use Think\Controller;
use Common\Org\Page;
class AlbumController extends AdminBaseController
{
   public function index(){
       $db=M('album');
       $p=I('get.p','','intval');
       $p=$p?$p:1;
       $limit=($p-1)*10;
       $limit=$limit.',10';
       $data=$db->order('time DESC')->limit($limit)->select();
       //获取相册包含图片数量
     if($data){
         foreach($data as $k => $v){
             $data[$k]['count']=M('photo')->where(array('aid' => $v['id']))->count();
             $data[$k]['username']=M('userinfo')->where(array('uid' => $v['uid']))->getField('username');
         }
     }
//       p($data);
       $count=$db->count();
       $page = new Page($count, 5);
       $this->assign('page',$page->show());
       $this->assign('data',$data);
       $this->display();
   }

    public function delAlbum(){
       if(IS_GET){
           $id=I('get.id','','intval');
           $albumModel=new AlbumModel();
           if($albumModel->del($id)){
             $this->success('删除成功',U('Admin/Album/index'));
           }else{
               $this->error('删除失败');
           }
       }
    }


    public function photo(){
        $db=M('photo');
        $p=I('get.p','','intval');
        $p=$p?$p:1;
        $limit=($p-1)*10;
        $limit=$limit.',10';
        $data=$db->order('time DESC')->limit($limit)->select();
        $count=$db->count();
//        p($data);
        $page = new Page($count, 5);
        $this->assign('page',$page->show());
        $this->assign('data',$data);
        $this->display();
    }

    public function delPhoto(){
     if(IS_GET){
         $id=I('get.id','','intval');
         if(M('photo') -> delete($id)){
             $this->success('删除相片成功',U('Admin/Album/photo'));
         }else{
             $this->error('删除失败');
         }
     }
    }
}