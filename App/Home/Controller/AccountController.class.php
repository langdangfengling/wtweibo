<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/20 0020
 * Time: 20:52
 */

namespace Home\Controller;
use Model\GuestViewModel;
use Think\Controller;
use Think\Page;
class AccountController extends CommonController
{
    /**
     * 用户留言处理，
     */
    public function guest()
    {
        /**
         * 读取该用户留言数据，展示在模板中
         */
        $where = array('uid' => session('user_id'));
        $guestView = new GuestViewModel();
        $count=$guestView->where($where)->count();
        $page=new Page($count,5);
        $limit=$page->firstRow.','.$page->listRows;
        $guest = $guestView->getAll($where, $limit);
//        dump($guest);die;
        //分页自定义样式
        $page->lastSuffix=false;//最后一页是否显示总页数
        $page->rollPage=4;//分页栏每页显示的页数
        $page->setConfig('prev','【上一页】');
        $page->setConfig('next','【下一页】');
        $page->setConfig('first','【首页】');
        $page->setConfig('last','【末页】');
        $page->setConfig('theme','共%TOTAL_ROW%条记录，当前是%NOW_PAGE%/%TOTAL_PAGE% %FIRST% %UP_PAGE% %DOWN_PAGE% %END%');

        $this->page=$page->show();
        $this->count=$count;
        $this->guest = $guest;
        $this->display();
    }
     //用户留言入库
    public function runGuest(){
        if(!IS_POST){
            $this->error('非法提交');
        }
        $data=array(
            'guest_uid' =>session('uid'),//留言者
            'content' =>I('post.content'),
            'guesttime' => time(),
            'uid' => session('user_id'),//留言所属用户
        );
        if(M('guest')->data($data)->add()){
            //异步读取刚刚留言的信息
            $user=M('userinfo')->where(array('uid' => $data['guest_uid']))->field(array('username','face60' => 'face'))->find();
            $user['guesttime']=$data['guesttime'];
            echo json_encode($user);
        }else{
            echo 'false';
        }
    }

}