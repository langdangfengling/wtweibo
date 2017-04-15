<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/27 0027
 * Time: 22:32
 */

namespace Model;


use Think\Model\ViewModel;

class ReplyViewModel extends ViewModel
{
     public $viewFields=array(
         'reply' =>array('id','content','uid','time','gid','_type' =>'left'),
         'userinfo' =>array('username','face60','_on' =>'userinfo.uid=reply.uid'),
     );
     public function getAll($where){
          $result=$this->where($where)->order('time DESC')->select();
          return $result;
     }
}