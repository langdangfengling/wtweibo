<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/12 0012
 * Time: 11:07
 */

namespace Home\Controller;


use Think\Controller;
use Think\Verify;
class LoginController extends Controller
{
    /*
     * 登录页面
     */
    public function index(){
//        如果存在session就直接跳转到首页
        if(isset($_SESSION['uid'])){
            redirect(__APP__);
        }
        $this->display();
    }
    /**
     * 处理登录页面
     */
    public function login(){
        if(!IS_POST){
            $this->error('非法请求');
        }else{
            //接收表单数据
            $account=I('post.account');
            $pwd=I('post.pwd','','md5');
            //验证是否查询到数据记录，以及密码是否正确
            $where=array('account' => $account,'password' => $pwd);
            $user=M('User')->where($where)->find();
            if(!$user) {
                $this->error('用户名或密码不正确');
            }
            //判断用户锁定状态
            if($user['lock']){
                $this->error('该用户已被锁定');
            }
            //登录成功将该账号缓存入文件
            $ip=get_client_ip();
            if(S('login_'.$ip)){
                //有缓存的话就先读取，再在缓存数据中加入,如果不先读取就只能存入一次数据
                $login_account=S('login_'.$ip);
                $id=$user['id'];
                $login_account[$id]=$account;
                S('login_'.$ip,$login_account,60*60*24*3);
            }else{
                //没有缓存，就存入
                $login_account=array();
                $id=$user['id'];
                $login_account[$id]=$account;
                S('login_'.$ip,$login_account,60*60*24*3);
            }
            //用户登录成功，则将 用户登录ip与账号异或运算加密保存到cookie中 处理下次自动登录,cookie加密函数enctyption
            if($_POST['auto']){
                $account=$user['account'];
                $value=$account.'|'.$ip;
                //调用加密函数
                $value=encryption($value);
                @setcookie('autoload',$value,C('AUTO_LOGIN_TIME'),'/');
            }
            //登录成功写入session 并跳转到用户个人页
            session('uid',$user['id']);
            header('Content-type:text/html;charse=utf-8');
            $this->redirect('User/index');
        }
    }
    /**
     * 注册页面
     */
    public function register(){
//        if(!C('REGIS_ON')){
//            $this->error('本网站暂停注册');
//        }
        $this->display();
    }
    /**
     * 注册提交
     */
    public function runRegis()
    {
        if (!IS_POST) {
            $this->error('非法请求');
        } else {
            //两次填写密码是否一致
            $pwd = I('post.pwd');
            $pwded = I('post.pwded');
            if ($pwd != $pwded) {
                $this->error('两次填写的密码不一致');
            }
            //收集数据
            //这里不能直接用create()直接接收数据，create()的作用：
//            1、将表单元素的值和数据库中的字段一一匹配。
//            2、讲数据库中没有的字段在数组中去除。所以uname昵称的信息会被过滤掉(username是userinfo表中的字字段，这里不能用create,这里需要关联添加
            $data = array(
                'account' => I('post.account'),
                'password' => I('post.pwded','','md5'),
                'registime' => $_SERVER['REQUEST_TIME'],
                'userinfo' => array(
                    'username' => I('post.uname'),
                )
            );
            $user = new \Model\UserModel();
            $id=$user->insert($data);
//            dump($id);
//            exit;
            if ($id) {
                //插入
                session('uid', $id);
                //注册成功，跳转到首页
                header('Content-type:text/html;charset=utf-8');
                $this->success('注册成功，正在为你跳转---', U('User/index'), 3);
            } else {
                $this->error('注册失败，请重试....');
            }
        }
    }
    //验证码生成
    public function verify(){
        $config=array(
            'fontSize'    =>    30,    // 验证码字体大小
            'length'      =>    4,     // 验证码位数
            'useNoise'    =>    false, // 关闭验证码杂点
            'fonttf'     => 'simhei.ttf',  //字体
        );
        $Verify=new Verify($config);
        //使用中文
        $Verify->useZh=true;
        //设置验证码字符
        $Verify->zhSet = '们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这';
        $Verify->entry();
    }
    //异步根据账户输入的找好得到该账户的图像80*80,从缓存文件里面读取
    public function getPic(){
//         if(!IS_AJAX){
//             $this->error('非法请求');
//         }
        $account1=I('post.account');
        $ip1=get_client_ip();
        if($array=S('login_'.$ip1)){
//            dump($array);die;
            //如果存在缓存文件，意思就是之前在该地址登录过
            if(in_array($account1,$array)){
                //得到用户的id
                $id=M('user')->where(array('account' => $account1))->getField('id');
                $userphoto=M('userinfo')->where(array('uid' => $id))->getField('face80');
               $data=array('status' => 1,'pic' => $userphoto);
                echo json_encode($data);
            }else{
                echo json_encode(array('status' => 0));
            }
        }else{
            echo json_encode(array('status' => 0));
        }
    }
    //异步请求验证账号
    public function checkAccount(){
        //是ajax请求才进的来
        if(!IS_AJAX){
            $this->error('非法请求');
        }
            //接收ajax请求传过来的数据
            $account=I('post.account');
            $where=array('account' =>$account);
            if(M('user')->where($where)->find()){
                echo 'false';
            }else{
                echo 'true';
            }
    }
    //异步请求验证昵称是否存在
    public function checkUname(){
        if(!IS_AJAX){
            $this->error('非法请求');
        }else{
            $uname=I('post.uname');
            $where=array('username' =>$uname);
            if(M('userinfo')->where($where)->getField()){
                echo 'false';
            }else{
                echo 'true';
            }
        }
    }
    //异步请求验证验证码是否错误
    public function checkVerify(){
        if(!IS_AJAX){
            $this->error('非法请求');
        }else{
            $code=I('post.verify');
            $Verify=new Verify();
            if($Verify->check($code)){
                echo 'true';
            }else{
                echo 'false';
            }
        }
    }
}
