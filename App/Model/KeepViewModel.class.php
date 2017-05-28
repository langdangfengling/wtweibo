<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/21 0021
 * Time: 15:20
 */

namespace Model;
use Think\Model\ViewModel;
class KeepViewModel extends ViewModel
{
    public $viewFields=array(
        'collect' => array('id' => 'kid','time'=>'ktime','_type' => 'inner','uid' => 'keep_uid'),
        'article' => array('id', 'content', 'isturn', 'time', 'turn', 'collect', 'readcount', 'comment', 'gid','name'=>'title',//read是关键字
            '_type' => 'inner' ,'_on' => 'collect.aid =  article.id'//左外连接查询,针对下个表有效
        ),
        'agroup' => array('name' => 'gname','uid','_type' => 'left','_on' => 'article.gid = agroup.id'),
        'userinfo' => array(
            'username', 'face60' => 'face', '_on' => 'agroup.uid=userinfo.uid',//连接查询条件
            '_type' => 'LEFT'
        ),
    );
    public function getAll($where, $limit)
    {
        $result = $this->where($where)->order('ktime DESC')->limit($limit)->select();
        foreach ($result as $k => $v) {
            if ($v['isturn']) {
                $articleView=new ArticleViewModel();
                //取得转发微博所有内容
                $turn =$articleView->where(array('id' => $v['isturn']))->field(array('username','turn','id','uid'))->find();
                //重组结果将原微博内容添加到转发微博中
                $result[$k]['isturn'] = isset($turn)?$turn:-1;
            }
        }
        return $result;
    }
}