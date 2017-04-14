<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/20 0020
 * Time: 17:45
 */

namespace Home\Controller;

use Model\WeiboViewModel;
use Think\Controller;
use Think\Page;

class SearchController extends CommonController{
    /**
     * 首页搜索功能,搜索用户
     */
    public function searchUser(){
        $keyword=$this->_getKeyword();//定义一个私有方法获得搜索关键字
//        dump($keyword);die;
        if($keyword){
            $db=M('userinfo');
            //在数据库中找出带有keyword的微博用户,除了自己
            $where=array(
                'username' => array('LIKE','%'.$keyword.'%'),
                'uid' =>array('NEQ',session('uid')),// NEQ 不等于
            );
            $field=array('username','sex','face80','follow','fans','location','article','intro','uid');
            $count=$db->where($where)->count();//获得记录总条数
            $page=new Page($count,5);//实例化page类，传入总记录数，每页显示条数
            $limit=$page->firstRow.','.$page->listRows;
            $result=$db->where($where)->field($field)->limit($limit)->select();
            //分页自定义样式
            $page->lastSuffix=false;//最后一页是否显示总页数
            $page->rollPage=4;//分页栏每页显示的页数
            $page->setConfig('prev','【上一页】');
            $page->setConfig('next','【下一页】');
            $page->setConfig('first','【首页】');
            $page->setConfig('last','【末页】');
            $page->setConfig('theme','共%TOTAL_ROW%条记录，当前是%NOW_PAGE%/%TOTAL_PAGE% %FIRST% %UP_PAGE% %DOWN_PAGE% %END%');
//            dump($result);die;
            //重新组合结果集得到是否相互关注或已关注
            $result=$this->_getMutual($result);
            //分配搜索结果到视图
//            dump($result);die;
            $this->result=$result;
            $this->page=$page->show();
        }
        //载入搜索页面
        $this->keyword=$keyword;
        $this->display();
    }

    //从首页返回搜索关键字到搜索页面
    private function _getKeyword(){
        return I('get.keyword')=="搜索微博、找人"?NULL:I('get.keyword');
    }

    //对结果集进行重新组合
    private function _getMutual($result){
        if(!$result) return false;
        foreach ($result as $k => $v) {
            $sql = '(SELECT follow FROM wt_follow WHERE follow=' . $v['uid'] . ' and fans=' . session('uid') . ')UNION (SELECT follow FROM wt_follow WHERE follow=' . session('uid')
                . ' AND fans=' . $v['uid'] . ')';
            $mutualt = M('follow')->query($sql);
//          dump($mutualt);die;
            if (count($mutualt) == 2) {
                $result[$k]['mutual'] = 1;//表示已经互相关注,有两条记录的情况下
                $result[$k]['followed'] = 1;//在互相关注的情况下肯定也意味着我已关注了对方，也可这个设个状态;
            } else {
                //未互相关注，那么在此情况下就需要检索我是否关注了对方
                $where = array('follow' => $v['uid'] , 'fans' => session('uid'));
                $result[$k]['followed']= M('follow')->where($where)->count();//如果有1条记录就表明我已关注对方，0的话就表示我没有关注对方
            }
        }
        return $result;
    }
}