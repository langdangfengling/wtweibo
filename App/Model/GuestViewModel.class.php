<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/19 0019
 * Time: 13:40
 */

namespace Model;


use Think\Model\ViewModel;

class GuestViewModel extends ViewModel
{
   public $viewFields=array(
       'guest' => array('id','content','guesttime','guest_uid','uid','_type' => 'left'),
       'userinfo' => array('username' => 'gusername','face60' => 'face','_on' =>'guest.guest_uid=userinfo.uid'),
   );
     public function getAll($where='',$limit=''){
         $result=$this->where($where)->order('guesttime DESC')->limit($limit)->select();

         if($result){
             $replyView=new \Model\ReplyViewModel();
             foreach($result as $k =>$v){
               $result[$k]['reply'] =$replyView->getAll(array('gid' =>$v['id']));
//                 dump($result[$k]['reply']);
             }
         }
         return $result;
     }
}