<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/11 0011
 * Time: 21:42
 */

namespace Model;


use Think\Model\ViewModel;

class CommentViewModel extends ViewModel
{
   public $viewFields=array(
       'comment' => array('id','content','time','uid','fid','aid'),
       'userinfo' => array('username','face60' =>'face','_on' => 'comment.uid = userinfo.uid'),
   );
    public function getAll($where,$limit){
        $result=$this->where($where)->order('time DESC')->limit($limit)->select();
        //需要对评论数据进行整合,将所有的评论回复递归到该评论下,通过评论表中的fid字段值来整合
        //我写的，太复杂了
//        if($result){
//            $result1=array();
//            foreach($result as $k => $v){
//               if($result[$k]['fid']==0){
//                   $result1[]=$result[$k];
//                }
//            }
//            for($i=0;$i<count($result1);$i++){
//                for($n=0;$n<count($result);$n++){
//                    if($result[$n]['fid']==$result1[$i]['id']){
//                        $result1[$i]['reply'][]=$result[$n];
//                    }
//                }
//            }
//        }
        foreach($result as $k => $v){
            $result[$k]['reply']=$this->where(array('fid' => $result[$k]['id']))->order('time DESC')->select();
        }
        return $result;
    }
}