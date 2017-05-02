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
use Model\ArticleViewModel;
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

        //读取用户发布文章数据
        //将我和我关注的用户所有的微博数据都得到，输出到模板中
        $uids=array(session('uid'));
        $where=array('funs'=>session('uid'));
        if($result=M('follow')->where($where)->field('follow')->select()){
//                  dump($result);die;
            //将我关注用户的id加入到$uids数组中
            foreach($result as $v){
                $uids[]=$v['follow'];
            }
        }
//       dump($uids);die;
        //调用微博视图模型得到所有数据
        $where=array(
            'uid'=>array('in',$uids),
        );
        $articleView=new ArticleViewModel();
        //分页显示
        $db=M('article');
        $count=$db->where($where)->count();
        $page=new Page($count,3);
        $limit=$page->firstRow.','.$page->listRows;
        $article=$articleView->getAll($where,$limit);
               // dump($article);               
//                需对文章内容稍作处理，存在数据库中的数据已经被转义，所以需要反转义回来,然后在截取一段字作文文章的描述
         foreach ($article as $k => $v) {
                    $article[$k]['content']=htmlspecialchars_decode($v['content']);//反转义
                     $article[$k]['content']=strip_tags( $article[$k]['content']);//去除字符串中html和php标签
                    $article[$k]['content']=substr($article[$k]['content'], 0,360);
                    }           
                  // dump($article);  die; 
        //右栏显示好友分组相关微博
        if($gid=I('get.gid','','intval')){
            //得到我关注好友的id
            $where=array('gid'=>$gid);
            $result=M('follow')->where($where)->select();
            $follow=array();
            if($result) {
                foreach ($result as $k => $v) {
                    $follow[$k] = $v['follow'];
                }
                $where = array('uid' => array('IN', $follow));
                $count=$db->where($where)->count();
                $page=new Page($count,3);
                $limit=$page->firstRow.','.$page->listRows;
                $article = $articleView->getAll($where, $limit);
            }else{
                $article=false;
                $count=0;
                $page=new Page($count,3);
                $limit=$page->firstRow.','.$page->listRows;
            }
        }
        //分页自定义样式
        $page->lastSuffix=false;//最后一页是否显示总页数
        $page->rollPage=4;//分页栏每页显示的页数
        $page->setConfig('prev','【上一页】');
        $page->setConfig('next','【下一页】');
        $page->setConfig('first','【首页】');
        $page->setConfig('last','【末页】');
        $page->setConfig('theme','共%TOTAL_ROW%条记录，当前是%NOW_PAGE%/%TOTAL_PAGE% %FIRST% %UP_PAGE% %DOWN_PAGE% %END%');
        $this->article=$article;
        $this->page=$page->show();
        $this->display();
    }

    public function saveArticle(){
        if(!IS_POST){
            $this->error('非法请求');
        }
//        var_dump($_POST);die;
        $data=array(
            'name' => I('post.title'),
            'content' =>I('post.content'),
            'gid' => I('post.gid'),
            'time' => time(),
        );
        if(M('article')->data($data)->add()){
            //用户发布文章数+1
            M('userinfo')->where(array('uid' => session('uid')))->setInc('article',1);
            $this->success('发布成功',$_SERVER['HTTP_REFERER']);
        }
    }
    //全文读取文章
    public function article(){
        if(!IS_GET){
            $this->error('非法请求');
        }
        $id=I('get.id');
        $ArticleView=new ArticleViewModel();
        $where=array('id' =>$id);
        $article=$ArticleView->where($where)->find();
        // dump($article);die;
        $this->article=$article;
        $this->display();   
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
            $follow_user=$db->where($where)->Field(array('uid','username','location','face60','sex','follow','fans','article'))->limit(5)->select();
        }
//        dump($follow_user);
        //得到我的粉丝用信息
        $fansids=M('follow')->where(array('follow' => $uid))->getField('fans',true);
        if($fansids){
            $where=array('uid' =>array('in',$fansids));
            $fans_count=count($fansids);
            $fans_user=$db->where($where)->Field(array('uid','username','location','face60','sex','follow','fans','article'))->limit(5)->select();
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
            $fields=array('uid','username','face60','sex','fans','follow','article');
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
//        echo $id;
        $albumModel=new AlbumModel();
        if($albumModel->del($id)){
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
        $count=$db->where(array('aid' => $id))->count();
        $page=new Page($count,15);
        $limit=$page->firstRow.','.$page->listRows;
        $photos=$db->where(array('aid' => $id))->order('time DESC')->limit($limit)->select();
//                dump($photos);die;
        //分页自定义样式
        $page->lastSuffix=false;//最后一页是否显示总页数
        $page->rollPage=4;//分页栏每页显示的页数
        $page->setConfig('prev','【上一页】');
        $page->setConfig('next','【下一页】');
        $page->setConfig('first','【首页】');
        $page->setConfig('last','【末页】');
        $page->setConfig('theme','共%TOTAL_ROW%条记录，当前是%NOW_PAGE%/%TOTAL_PAGE% %FIRST% %UP_PAGE% %DOWN_PAGE% %END%');

        $this->album1=$album1;
        $this->photos=$photos?$photos:false;
        $this->page=$page->show();
        $this->count=$count;
        $this->display();
    }

    //异步删除相片
    public function delPhoto(){
        if(!IS_AJAX){
            $this->error('非法请求');
        }
        $pid=I('post.pid','','intval');
        $db=M('photo');
        $photo150=$db->where(array('id' => $pid))->getField('photo150');
        $photo=$db->where(array('id' => $pid))->getField('photo');
        if($db->delete($pid)){
            unlink($photo150);
            unlink($photo);
            echo 1;
        }else {
            echo 0;
        }
    }

}