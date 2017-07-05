<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/8 0008
 * Time: 21:02
 */

namespace Model;


use Think\Model\ViewModel;

class UserViewModel extends ViewModel
{
   public $viewFields=array(
       'user' => array('id','registime','lock' => 'status','_type' => 'left'),
       'userinfo' => array('username','face60' => 'face','follow','fans','article','_on' => 'user.id=userinfo.uid'),
   );
}