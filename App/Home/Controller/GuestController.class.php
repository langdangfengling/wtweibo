<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/19 0019
 * Time: 8:31
 */

namespace Home\Controller;
use Model\GuestViewModel;
use Think\Controller;
class GuestController extends CommonController
{
      public function index(){
          //读取留言信息
          $where=array('uid' => session('uid'));
          $guestView=new GuestViewModel();
          $user=$guestView->getAll($where,'0,5');
//          dump($user);die;
          $this->user=$user;
          $this->display();
      }
}