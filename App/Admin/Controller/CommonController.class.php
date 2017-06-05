<?php
/**
 * 后台公共登录模块
 * wt
 */

namespace Admin\Controller;


use Think\Controller;
use Think\Verify;

class CommonController extends Controller
{
    /**
     * 登录
     */
    public function login(){
        if(IS_POST){
            $post=I('post.');
            if(!check_verify($post['captcha'])) $this->ajaxReturn(array('status'=>1, 'msg'=>'验证码错误'));
            $map['admin_name'] = $post['admin_name'];
            $admin_db = D('Admin');
            $data=$admin_db->where($map)->find();
            if (empty($data))
            {
                $this->ajaxReturn(array('status'=>1, 'msg'=>'账号不存在'));
            }
            else
            {
                if($data['admin_password'] != md5($post['admin_password']))
                {
                    $this->ajaxReturn(array('status'=>1, 'msg'=>'账号或密码错误'));
                }
                if($data['status'] != 1)
                {
                    $this->ajaxReturn(array('status'=>1, 'msg'=>'账号已被禁用，请联系管理员'));
                }
                $data['last_login_ip'] = get_client_ip();
                $data['last_login_time'] = time();
                session('admin', $data);
                $admin_db->save($data);//登录成功更新一下登录信息
                $this->ajaxReturn(array('status'=>0, 'msg'=>'登录成功'));
            }
        }else{
            $this->display();
        }
    }


    /**
     * 退出
     */
    public function logout(){
        session('admin', null);
        $this->success('退出成功',U('Common/login'),1);
    }


    /**
     * 验证码
     */
    public function verify(){
        $Verify=new Verify();
        $Verify->length   = 4;
        $Verify->fontSize = 40;
        $Verify->useNoise = false;
        $Verify->codeSet = '0123456789';
        $Verify->useCurve = false;
        $Verify->entry();
    }
}