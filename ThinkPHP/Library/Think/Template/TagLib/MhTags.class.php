<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/8 0008
 * Time: 17:33
 */

namespace Think\Template\TagLib;

use Think\Template\TagLib;
/**
 * MhTags标签库驱动
 */
class MhTags extends TagLib
{
   protected $tags=array(// 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
       //定义标签
       'userinfo' => array('attr' =>'id' ,'close' => 1),
       'friend' => array('attr' => 'uid', 'close' => 1),
   );

    /**
     * 读取用户信息标签
     */
    Public function _userinfo($tag,$content){
        $id = $tag['id'];
        $str = '';
        $str .= '<?php ';
        $str .= '$where = array("uid" => ' .$id . ');';
        $str .= '$field = array("username","face80" => "face","follow","fans","weibo","uid");';
        $str .= '$userinfo = M("userinfo")->where($where)->field($field)->find();';
        $str .='extract($userinfo);';
//        $str .='dump($userinfo);';
        $str .='?>';
        $str .= $content;
        return $str;
    }

    public function _friend($tag,$content){
        $uid=$tag['uid'];
        $str = '';
        $str .= '<?php ';
        $str .= '$db=M("follow");$uid='.$uid.';';
        $str .= '$follow=$db->where(array("funs"=>$uid))->getField("follow",true);';
        $str .= '$follow=implode(\',\',$follow);';
//        $str .= 'dump($follow);die;';
        $str .= 'if($follow): ;';
        $str .='$sql="SELECT `uid`,`username`,`face50` as `face`,COUNT(f.`follow`) AS `count` FROM `wt_follow` AS f  LEFT JOIN `wt_userinfo` AS u
                ON f.`follow`=u.`uid` WHERE f.`funs` IN (" . $follow . ") AND f.`follow` NOT IN (".$follow.") AND f.`follow` !=" .$uid. " GROUP BY f.`follow` ORDER BY `count` DESC LIMIT 4";';
        $str .='$friend=$db->query($sql);';
//        $str .='echo $db->getLastSql();';
        $str .='foreach($friend as $v) : ;';
        $str .='extract($v);';
        $str .= ' ?>';
        $str .=$content;
        $str .='<?php endforeach; endif; ?>';
       return $str;
    }
 }