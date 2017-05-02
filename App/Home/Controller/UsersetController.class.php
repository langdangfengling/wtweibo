<?php
/**
 * 用户账号设置控制器
 * User: wt
 * Date: 2016/11/13 0013
 * Time: 8:24
 */

namespace Home\Controller;
use Think\Controller;

class UsersetController extends CommonController
{
    public function index(){
        //从数据库中获取需要更改的数据
        $uid=$_SESSION['uid'];
        $where=array('uid'=>$uid);
        $fields=array('username','sex','profession','location','constellation','intro','face230','face80','face60');//需要的字段
        $userinfo=M('userinfo') ->field($fields)->where($where)->find();
//       dump($userinfo);
//       die;
        //载入账号设置页面
        //$this->assign('user',$userinfo);
        $this->user=$userinfo;//与上一样，这是thinkphp用法，上是smarty模板用法
        //模板样式设置
        $this->assign('style','default');
        $this->display();
    }

    //用户设置数据后更新到数据库中
    public function editBasic()
    {
        if (!IS_POST) {
            $this->error('非法请求');
        }
        //接收数据
        $data = array(
            'username' => I('post.nickname'),
            'profession' => I('post.profession'),
            'sex' => I('post.sex'),
            'location' =>I('post.province').' '.I('post.city'),
            'constellation' =>I('post.night'),
            'intro' => I('post.intro'),
        );
        //dump($data);
        $where = array('uid' => $_SESSION['uid']);
        if (M('userinfo')->where($where)->save($data)) {
            $this->success('修改成功', U('index'),2);
        } else {
            $this->error('修改失败');
        }
    }
    public function editFace(){
        if(!IS_POST){
            $this->error('非法请求');
        }else{
            //将缩略图以路径字符串形式更新入库
            //收集数据
            $data=array(
                'thumb' =>I('post.thumb'),
                 'face230' =>I('post.face230'),
                'face80' =>I('post.face80'),
                'face60' =>I('post.face60'),
            );
            $where=array('uid' =>session('uid'));
            //假如该用户数据库中已经存放图像，就将其删除，在更新
            $oldface=M('userinfo')->where($where)->field(array('face60','face80','face230'))->find();
            if($oldface){
                //删除原图片
                unlink($oldface['face60']);
                unlink($oldface['face80']);
                unlink($oldface['face230']);
            }
            if(M('userinfo')->where($where)->save($data)){
                $this->success('图像上传成功',U('index'));
            }else{
                $this->error('图像上传失败');
            }
        }
    }
    //修改密码
    public function editPwd(){
        if(!IS_POST) {
            $this->error('非法请求');
        }
        $where=array('id' =>session('uid'));
        //获取旧的密码
        $oldpwd=M('user')->where($where)->field('password')->find();
//          dump($oldpwd);die;
        if(I('post.old','','md5')!=$oldpwd['password']){
            $this->error('你填写的密码不正确');
        }
        $newpwd=I('post.newed','','md5');
        if(M('user')->where($where)->setField('password',$newpwd)){
            $this->success('修改密码成功',U('index'));
        }else{
            $this->error('修改密码失败');
        }
    }

}
