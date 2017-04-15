<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController
{
    /**
     * 首页
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 阅读全文
     */
    public function article(){
        $this->display();
    }

    /**
     * 退出登录操作
     */
    public function loginOut(){
        //删除session变量
        session_unset();
        //销毁session数据区
        session_destroy();
        //删除自动登录的cookie值
        @setcookie('autoload','',time()-3600,'/');
        //跳转至登录页面
        redirect(U('Login/index'));
    }
}