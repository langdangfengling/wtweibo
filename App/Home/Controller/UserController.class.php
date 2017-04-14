<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/19 0019
 * Time: 17:52
 */

namespace Home\Controller;
use Model\AlbumModel;
use Model\AlbumViewModel;
use Model\GuestViewModel;
use Think\Controller;
use Think\Page;
class UserController extends CommonController
{
  /**
   * 载入个人用户页
   */
    protected $userinfo;
    public function index(){
        //进入其他用户主页
        if(isset($_GET['uid']) && $_GET['uid'] != session('uid')) {
            $uid = I('get.uid', '', 'intval');
            session('user_id',$uid);
            $this->display('Account/index');
        }
        //存入session中，在right.html中统一获取用户数据，避免其他页面无法获得用户信息
        $this->display();
        //读取用户发布微博数据
//        $viewModel=new WeiboViewModel();
//        $where=array('uid' => $uid);
//        //分页显示
//        $count=M('weibo')->where($where)->count();
//        $page=new Page($count,3);
//        $limit=$page->firstRow.','.$page->listRows;
//        $weibo=$viewModel->getAll($where,$limit);
////                dump($weibo);die;
//        //分页自定义样式
//        $page->lastSuffix=false;//最后一页是否显示总页数
//        $page->rollPage=4;//分页栏每页显示的页数
//        $page->setConfig('prev','【上一页】');
//        $page->setConfig('next','【下一页】');
//        $page->setConfig('first','【首页】');
//        $page->setConfig('last','【末页】');
//        $page->setConfig('theme','共%TOTAL_ROW%条记录，当前是%NOW_PAGE%/%TOTAL_PAGE% %FIRST% %UP_PAGE% %DOWN_PAGE% %END%');
//        $this->weibo=$weibo;
//        $this->page=$page->show();
    }

    /**
     * 我的好友
     */
    public function friend(){
        //获取我的好友信息
        $db=M('userinfo');
        $uid=session('uid');
        //得到我关注用户的信息
        $followids=M('follow')->where(array('fans' =>$uid ))->getField('follow',true);
//        dump($followids);die;
        if($followids){
           $where=array('uid' =>array('in',$followids));
            $follow_count=count($followids);
            $follow_user=$db->where($where)->Field(array('uid','username','location','face80','sex','follow','fans','article'))->limit(5)->select();
        }
//        dump($follow_user);
        //得到我的粉丝用信息
        $fansids=M('follow')->where(array('follow' => $uid))->getField('fans',true);
        if($fansids){
            $where=array('uid' =>array('in',$fansids));
            $fans_count=count($fansids);
            $fans_user=$db->where($where)->Field(array('uid','username','location','face80','sex','follow','fans','article'))->limit(5)->select();
        }
        $this->followids=$followids?$followids:null;
        $this->fansids=$fansids?$fansids:null;
        $this->follow_count=$follow_count?$follow_count:0;
        $this->fans_count=$fans_count?$fans_count:0;
        $this->follow_user=$follow_user;
        $this->fans_user=$fans_user;
        $this->display();
    }
    //用户粉丝，关注列表,用同一个方法进行处理
    public function followList(){
        $type=I('get.type','','intval');//获得传过来的type以确定是得到粉丝还是关注用户列表
        $uid=I('get.uid','','intval');
        //获取关注，粉丝用户id
        $where=$type?array('fans' =>$uid):array('follow' => $uid);
        $field=$type?'follow':'fans';
        $db=M('follow');
        $count=$db->where($where)->count();
//        dump($count);
        $page=new Page($count,5);
        $limit=$page->firstRow.','.$page->listRows;
        $uids=$db->where($where)->limit($limit)->select();
        if($uids) {
            foreach ($uids as $key => $val) {
                $uids[$key] = $val[$field];
            }
//            dump($uids);die;
            $where=array('uid'=>array('in',$uids));
            $fields=array('uid','username','face80','sex','fans','follow','article');
            $users=M('userinfo')->where($where)->field($fields)->select();
//            dump($users);die;
            $this->users=$users;
        }
        $follow=M('follow')->where(array('fans'=>$uid))->field('follow')->select();
        if($follow){
            foreach ($follow as $k => $v) {
                $follow[$k]=$v['follow'];
            }
        }
//        dump($follow);
        $fans=M('follow')->where(array('follow'=>$uid))->field('fans')->select();
        if($fans){
            foreach($fans as $k => $v){
                $fans[$k] = $v['fans'];
            }
        }
//        dump($fans);die;
        $this->type=$type;
        $this->count=$count;
        $this->follow1=$follow;
        $this->fans1=$fans;
        $this->page=$page->show();
        $this->display('followList');
    }

    /**
     * 留言信息读取/包括评论信息
     */
    public function guest(){
        /**
         * 读取该用户留言数据，展示在模板中
         */
        //留言分页显示
        $where = array('uid' => session('uid'));
        $guestView = new GuestViewModel();
        $count=$guestView->where($where)->count();
        $page=new Page($count,5);
        $limit=$page->firstRow.','.$page->listRows;
        $guest = $guestView->getAll($where, $limit);
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
    //删除留言
    public function delGuest(){
      if(!IS_AJAX){
          $this->error('非法提交!');
      }
      //接收异步传输过来的数据
        $id=I('post.gid','','intval');
        if(M('guest')->delete($id)){
            //删除成功同时删除该条留言相关回复内容
                $rid=M('reply')->where(array('gid' => $id))->getField('id',true);
            if($rid){
                $rids=implode(',',$rid);
                M('reply')->delete($rids);
            }
            echo 1;
        }else{
            echo 0;
        }
    }
    //回复异步处理
    public function reply(){
        if(!IS_AJAX){
            $this->error('非法请求!');
        }
        $data=array(
            'uid' => session('uid'),
            'content' => I('post.content'),
            'gid' =>I('post.gid','','intval'),
            'time' => time(),
        );
        if($id=M('reply')->data($data)->add()){
            $db=M('userinfo');
            $data['username']=$db->where(array('uid' => $data['uid']))->getField('username');
            $data['face60']=$db->where(array('uid' => $data['uid']))->getField('face60');
            $data['content']=replace_weibo($data['content']);
            $data['status'] = 1;
            $data['id']=$id;
            $data['time']=time_format($data['time']);
            echo json_encode($data);
        }else{
            echo json_encode(array('status' => 0,'msg' =>'回复失败!')) ;
        }
    }
    //异步删除回复
    public function delReply(){
        if(!IS_AJAX){
            $this->error('非法提交');
        }
        $rid=I('post.rid','','intval');
        if(M('reply')->delete($rid)){
            echo 1;
        }else{
            echo 0;
        }
    }

    /**
     * 说说处理
     */
    //说说页面读取
    public function talk(){
        $talk=M('talk')->where(array('uid' => session('uid')))->select();
//        dump($talk);die;
        $this->talk=$talk?$talk:false;
        $this->display();

    }
    //发布说说
    public function sendTalk(){
        if(!IS_POST){
            $this->error('非法请求');
        }
      $data=array(
          'uid' => session('uid'),
          'content' => I('post.content'),
          'time' => time(),
      );
        if(M('talk')->data($data)->add()){
            $this->success('说说发布成功!',U('User/talk'));
        }else{
            $this->error('说说发布失败!');
        }
    }
    //删除说说
    public function delTalk(){
        if(!IS_GET){
            $this->error('非法提交');
        }
        $id=I('get.id','','intval');
        if(M('talk')->delete($id)){
            $this->success('删除成功!',U('User/talk'));
        }else{
            $this->error('删除失败!');
        }
    }

    /**
     * 用户相册处理
     */
    //读取相册
    public function album(){
        $album=M('album')->where(array('uid' => session('uid')))->order('time DESC')->select();
        $this->album=$album?$album:false;
        $this->display();
    }
    //异步创建相册
    public function createAlbum(){
        if(!IS_AJAX){
            $this->error('非法请求');
        }
        $data=array(
            'name' => I('post.name'),
            'describe' =>I('post.depict'),
            'time' =>time(),
            'uid' => session('uid'),
        );
        if($aid=M('album')->data($data)->add()){
            echo json_encode(array('aid' => $aid,'status' => 1));
        }else{
            echo json_encode(array('msg' => '创建失败','status' =>0));
        }
      }
    //异步编辑相册
    public function editAlbum(){
      if(!IS_POST){
          $this->error('非法请求');
      }
        $data=array(
            'id' =>I('post.aid','','intval'),
            'name' => I('post.name1'),
            'describe' =>I('post.depict'),
        );
//        dump($data);die;
        if(M('album')->save($data)){
            $this->redirect('User/album');
        }else{
            $this->error('保存失败');
        }
    }
    //异步获取相册信息，用来编辑
    public function getAlbum(){
        if(!IS_AJAX){
            $this->error('非法请求');
        }
        $id=I('post.aid','','intval');
        $album=M('album')->where(array('id' => $id))->find();
        if($album){
            echo json_encode($album);
        }else{ echo 'false';}
    }

    //异步删除相册
    public function delAlbum(){
        if(!IS_AJAX){
            $this->error('非法请求');
        }
        $id=I('post.aid','','intval');
        $albumModel=new AlbumModel();
        if($albumModel->delete($id)){
            echo 1;
        }else{
            echo 0;
        }
    }

    //通过点击相册进入到该相册模板页,展示相片
    public function photo(){
        if(!IS_GET){
            $this->error('非法请求');
        }
        $id=I('get.aid','','intval');
//        dump($id);die;
       //获取相册信息
        $album1=M('album')->where(array('id' => $id))->find();
        $db=M('photo');
        $photos=$db->where(array('aid' => $id))->order('time DESC')->select();
//        dump($photos);die;
        $count=$db->where(array('aid' => $id))->count();
        $this->album1=$album1;
        $this->photos=$photos?$photos:false;
        $this->count=$count;
        $this->display();
    }

}