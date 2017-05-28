<?php
/**
 * 私信视图模型
 */

namespace Model;


use Think\Model\ViewModel;

class LetterViewModel extends ViewModel
{
    public $viewFields=array(
        'letter' => array('id','from' => 'lid','content','time','uid','_type'=>'left'),
        'userinfo' =>array('username','face60' => 'face','_on' => 'letter.from=userinfo.uid'),
    );

    public function getAll($where,$limit){
        $result=$this->where($where)->order('time DESC')->limit($limit)->select();
        return $result;
    }

}