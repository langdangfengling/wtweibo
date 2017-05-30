<?php
/**
 * 共用控制器
 * Created by wt
 * User: Administrator
 * Date: 2016/11/12 0012
 * Time: 15:58
 */

namespace Home\Controller;

use Model\ArticleViewModel;
use Think\Controller;
use Think\Image;
use Think\Upload;
use Model\CommentViewModel;

class CommonController extends Controller
{
    /**
     * 自动运行的方法
     */
    public function _initialize()
    {
        //处理自动登录
        if (isset($_COOKIE['autoload']) && !isset($_SESSION['uid'])) {
            $value = explode('|', encryption($_COOKIE['autoload'], 1));
            //获取现在用户登录的ip与保存在cookie中的Ip比较，是否同一ip登录
//            dump($value);
//            die;
            $ip = get_client_ip();
            if ($ip == $value[1]) {
                $account = $value[0];
                $where = array('account' => $account);
                $user = M('User')->where($where)->find();
//                dump($user);
//                die();
                if ($user && !$user['lock']) {
//                    $account=$user['account'];
//                    $pwd=$user['password'];
                    session('uid', $user['id']);
//                    $login=A('Login');
//                    $login->assign('account',$account);
//                    $login->assign('pwd',$pwd);
//                    $login->display('Login:index');
//                    die;
                }
            }
        }
//        dump($_SESSION['uid']);
        //判断用户是否已登录
        if (!isset($_SESSION['uid'])) {
            header('Content-type:text/html;charset=utf-8');
            redirect(U('Index/index'), 3);
        }
    }
    /**
     * 获取右侧用户信息
     */
    public function getUser()
    {
        $uid=I('get.uid');
//        dump($uid);
    }

        //图像上传
    public function uploadFace()
    {
        if (!IS_POST) {
            $this->error('非法提交');
        }
        //对提交过来的图像,用Upload类来处理
        $uploadinfo = $this->_upload('face/');
//        print_r($uploadinfo);
        if (is_array($uploadinfo)) {
            $img1 = 'Uploads/' . $uploadinfo['Filedata']['savepath'] . $uploadinfo['Filedata']['savename'];
            $saveName = $uploadinfo['Filedata']['savename'];//上传图片名称
            $savePath = 'Uploads/' . $uploadinfo['Filedata']['savepath'];//上传图片保存路径
            $img = $this->_image($img1, $savePath, $saveName);//调用处理缩略图方法
            echo json_encode($img);//将处理信息以json数据串形式返回
        }
    }
//    /*
//   * 微博图片上传
//   */
//    public function uploadPic(){
//        if(!IS_POST){
//            $this->error('非法提交');
//        }
//        //对提交过来的图片用upload类处理
//        $uploadinfo=$this->_upload('weibo/');
////        print_r($uploadinfo);
//        if(is_array($uploadinfo)){
//            $img2='Uploads/'.$uploadinfo['Filedata']['savepath'].$uploadinfo['Filedata']['savename'];
//            $saveName=$uploadinfo['Filedata']['savename'];//上传图片名称
//            $savePath='Uploads/'.$uploadinfo['Filedata']['savepath'];//上传图片保存路径
//            $img=$this->_thumb($img2,$savePath,$saveName);//调用处理缩略图方法
//            echo json_encode($img);//将处理信息以json数据串形式返回
//        }
//    }


    /**
     * 相册图片上传
     */
    public function sendPhoto(){
        if(!IS_AJAX){
            $this->error('非法提交');
        }
//        var_dump($_FILES);
        $aid=I('post.aid','','intval');
        $db=M('photo');
        $data=array();
        $photoinfo=$this->_upload('photos/');
//        var_dump($photoinfo);die;
//        echo json_encode($photoinfo);
        if(is_array($photoinfo)){
            foreach($photoinfo as $k => $v){
                $photo='Uploads/' . $v['savepath'] . $v['savename'];
                $savePath='Uploads/' . $v['savepath'];
                $thumb=$this->_photoImg($photo,$savePath,$v['savename']);
                $data['photo']=$savePath.$v['savename'];
                $data['photo150']=$thumb;
                $data['aid']=$aid;
                $name=strstr($v['name'],'.',true);
                $data['name']=$name;
                //入库
                $pid=$db->data($data)->add();
                if(!$pid){
                    echo json_encode(array('status' =>0,'msg' => '上传失败'));
                    return false;
                }
            }
        }
        echo json_encode(array('status' => 1,'msg' => '上传成功!'));
    }
    //相册图片缩略图处理
    private function _photoImg($photo,$savePath,$saveName){

        if(!$photo) {
            return array('status' => 0, 'msg' => '上传图片失败，请重试!');//status为上传状态，0，失败 1，成功 msg,返回信息
        }else{
            $image=new Image();
            $image->open($photo);
            $image->thumb(173,130)->save($savePath.'thumb_170'.$saveName);
            return $savePath.'thumb_170'.$saveName;
        }
    }
    /**
     * 处理用户图像上传
     */

    private function _upload($path){
        //实例化upload类
        $upload=new Upload();
        $upload->maxSize=2000000;//上传文件大小
        $upload->rootPath=C('UPLOAD_PATH');//上传文件根目录
        $upload->savePath=$path;//相对于更目录保存的路径，相当于对保存文件进行一个目录分类
        $upload->saveName= array('uniqid','');;//文件名保存规则，唯一性.
        $upload->replace=true;//文件名相同时进行替换
        $upload->exts=array('jpg','jpeg','gif','png');//允许上传文件的后缀名
        $upload->mimes=array('image/jpg','image/jpeg','image/gif','image/png');
        $upload->autoSub=true;
        $upload->subName=array('date','Ymd');//保存路径下面的子目录，按日期进分类管理
        $info=$upload->upload();
        if(!$info){
            return  $this->error($upload->getError());
        }else{
            return $info;
        }
    }

    //图像缩略图处理
    /**
     * @param $img1 所要处理的图像，保存的是图像完整的路径信息
     * @param $savePath 图像保存的地址
     * @param $saveName  图像名
     * @return mixed
     */
    private function _image($img1,$savePath,$saveName){
        //判断是否有图片上传
        if(!$img1){
            return array('status'=>0,'msg'=>'上传图片失败，请重试!');//status为上传状态，0，失败 1，成功 msg,返回信息
        }else {
            $image = new Image();
            $image->open($img1);//打开图像
            $thumb = $savePath . $saveName;
            $thumb230 = $image->thumb(230, 230)->save($savePath . 'thumb230_' . $saveName);
            $thumb80 = $image->thumb(80, 80)->save($savePath . 'thumb80_' . $saveName);
            $thumb60 = $image->thumb(60, 60)->save($savePath . 'thumb60_' . $saveName);
            return array(
                'status' => 1,
                'path' => array(
                    'thumb' => $savePath . $saveName,
                    'thumb230' => $savePath . 'thumb230_' . $saveName,
                    'thumb80' => $savePath . 'thumb80_' . $saveName,
                    'thumb60' => $savePath . 'thumb60_' . $saveName,
                ),
            );
        }
    }
    //微博图片缩略图处理
    /**
     * @param $img2 所要处理的微博图片，保存的是图像完整的路径信息
     * @param $savePath 微博图片保存的地址
     * @param $saveName  微博图片名
     * @return mixed
     */
    private function _thumb($img2,$savePath,$saveName){
        //判断是否有图片上传
        if(!$img2){
            return array('status'=>0,'msg'=>'上传图片失败，请重试!');//status为上传状态，0，失败 1，成功 msg,返回信息
        }else {
            $image = new Image();
            $image->open($img2);//打开图像
//            $thumb = $savePath . $saveName;
            $thumb800 = $image->thumb(800, 800)->save($savePath . 'thumb800_' . $saveName);
            $thumb380 = $image->thumb(380,380)->save($savePath . 'thumb380_' . $saveName);
            $thumb120 = $image->thumb(120, 120)->save($savePath . 'thumb120_' . $saveName);
            return array(
                'status' => 1,
                'path' => array(
//                    'thumb' => $savePath . $saveName,
                    'thumb800' => $savePath . 'thumb800_' . $saveName,
                    'thumb380' => $savePath . 'thumb380_' . $saveName,
                    'thumb120' => $savePath . 'thumb120_' . $saveName,
                )
            );
        }
    }
    //异步添加用户分组
    public function addGroup(){
        if(!IS_AJAX){
            $this->error('非法提交');
        }
        $data['name']=I('post.name');
        $data['uid']=session('uid');
        if(M('group')->data($data)->add()){
            echo json_encode( array('status' => 1,'msg' => '创建分组成功'));
        }else{
            echo json_encode(array('status' => 0,'msg' =>'创建分组失败'));
        }
    }
    //异步添加用户关注
    public function addFollow(){
        if(!IS_AJAX){
            halt('该页面不存在');
        }
        $data=array('gid'=> I('post.gid','','intval'),
            'follow' => I('post.follow','','intval'),
            'fans' =>(int)session('uid'),
        );
        if(M('follow')->add($data)){
            $db=M('userinfo');
            //粉丝数+1 关注的用户他里面粉丝数要加1
            $db->where(array('uid' => $data['follow']))->setInc('fans',1);
            //关注数+1
            $db->where(array('uid' => session('uid')))->setInc('follow',1);
            echo json_encode(array('status' => 1,'msg' => '关注成功!'));
        }else{
            echo json_encode(array('status' => 0,'msg' => '关注失败,请重试!'));
        }
    }
    //异步移除关注与粉丝  1为移除关注，0为移除粉丝
    public function delFollow(){
        if(!IS_AJAX){
            halt('请求的页面不存在');
        }
        $uid=I('post.uid','','intval');
        $type=I('post.type','','intval');
        $where=$type?array('follow' =>$uid,'fans' =>session('uid')):array('follow' =>session('uid'),'fans' =>$uid);
        if(M('follow')->where($where)->delete()){
            //对应的关注数与粉丝数要减少
            $db=M('userinfo');
            if($type){
                $db->where(array('uid' => session('uid')))->setDec('follow');//本用户关注数减1
                $db->where(array('uid' => $uid))->setDec('fans');//相应的移除用户粉丝减1
            }else{
                $db->where(array('uid' => session('uid')))->setDec('fans');//本用户粉丝减1
                $db->where(array('uid' => $uid))->setDec('follow');//相应的被删除粉丝的用户他的关注数减1
            }
            echo 1;
        }else{
            echo 0;
        }
    }
    /**
     * 异步添加文章分类
     */
    public function addArticleGroup(){
        if(!IS_AJAX){
            $this->error('非法请求');
        }
       $data=array('name' => I('post.name'),'uid' =>session('uid'));
//        echo json_encode($data);die;
        $gid=M('agroup')->data($data)->add();
        if($gid){
          echo json_encode(array('status' => 1,'gid' => $gid));
        }else{
            echo json_encode(array('status' => 0));
        }
    }

//    /**
//     * 用户模板设置
//     */
//    public function editTpl()
//    {
//        if (!IS_AJAX) {
//            $this->error('非法提交');
//        }
//        $data = array(
//            'style' => I('post.tpl'),
//        );
//        if (M('userinfo')->where(array('uid' => session('uid')))->save($data)) {
//            echo 1;
//        } else {
//            echo 0;
//        }
//    }
    /*
     * 异步推送消息
     */
    public function getMsg(){
        $uid=session('uid');
        //从内从中获取数据
        $msg=S('userMsg'.$uid);
        if($msg){
            //如果存在
            if($msg['comment']['status']){
                //存在推送消息
                $msg['comment']['status']=0;//进来了表示已推送到页面显示，然后状态设置为0；
                S('userMsg'.$uid,$msg,0);
                echo json_encode(array(
                    'status' => 1,
                    'total' => $msg['comment']['total'],
                    'type' => 1,
                ));
                exit;
            }
            if($msg['letter']['status']){
                //存在推送消息
                $msg['letter']['status']=0;
                S('userMsg'.$uid,$msg,0);
                echo json_encode(array(
                    'status' => 1,
                    'total' => $msg['letter']['total'],
                    'type' => 2,
                ));
                exit;
            }
            if($msg['atme']['status']){
                //存在推送消息
                $msg['atme']['status']=0;
                S('userMsg'.$uid,$msg,0);
                echo json_encode(array(
                    'status' => 1,
                    'total' => $msg['atme']['total'],
                    'type' => 3,
                ));
                exit;
            }
        }
        echo json_encode(array('status' =>0));
    }
    //文章评论数据异步获取  更简洁的实现，不用大量拼接字符串，将评论数据展示区重新单独建立个页面，然后异步更新此页面的数据，最后替换到文章页面的评论数据区
    public function getComments(){
       if(!IS_AJAX){
           $this->error('非法请求');
       }
        $page=I('get.page','','intval')?I('get.page','','intval'):1;
        $offset=($page-1)*5;
        $id=I('get.aid','','intval');//文章id
        //评论数据
        $where=(array('aid' => $id,'fid' => 0));//这里一定要带上条件fid=0，然后commentview类里对评论重新组合
        $commentView=new CommentViewModel();
        $count=$commentView->where($where)->count();
        //导入第三方分页显示类库
        import("Common.Org.PageAjax");
        $Page = new \PageAjax($count,5,$page,2);//这里文章页显示的评论数据是自然加载，默认显示第一页数据，此时的都不带a连接，这里如果想异步获取数据，那么就需要再写一个方法来异步获取评论，并传递page（当前页）参数
//        dump($count);die;
        $limit=$offset.',5';
        $comments=$commentView->getAll($where,$limit);
//        var_dump($comments);die;
        $this->count=$count;
        $this->comments=$comments;
        $this->assign('page',$Page->myde_write());
        $this->display('comments');
    }
    //index中文章异步删除
    public function delArticle(){
        if(!IS_AJAX){
            $this->error('非法提交');
        }
        $aid=I('post.aid','','intval');
//        echo $aid;
        $preg="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
        $content=M('article')->where(array('id' => $aid))->getField('content');
        $content=htmlspecialchars_decode($content);
//        var_dump($content);
        preg_match_all($preg,$content,$src);
        if(M('article')->delete($aid)){
            //删除微博中存在的图片
//          //删除微博中存在的图片
            if(is_array($src) && !empty($src)){
                foreach($src[1] as $v){
                    $v=substr($v,9,255);
                    unlink($v);
                }
            }
            //该用户对应的发布文章数减1
            M('userinfo')->where(array('uid' => $_SESSION['uid']))->setDec('article',1);
            //对应文章评论，收藏都应该删除
            M('comment')->where(array('aid' => $aid))->delete();
            M('collect')->where(array('aid' => $aid))->delete();
            echo 1;
        }else{
            echo 0;
        }
    }
    //right.html 中推荐，最新，随机文章获取
    public function getArticle(){
        if(!IS_AJAX){
            $this->error('非法请求');
        }
        $str=I('post.str');
        //将我和我关注的用户所有的文章数据都得到
        $uid=I('post.uid','','intval');
        $uids=array($uid);
        $where=array('fans'=>$uid);
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
        $count=$articleView->where($where)->count();
        $num=rand($count);
        $limit=$num.','.'5';
        switch ($str) {
            case 'new':
               $article=$articleView->where($where)->field(array('id','title','isturn'))->order('time DESC')->limit(5)->select();
                break;
            case 'hot':
                $article=$articleView->where($where)->field(array('id','title','isturn'))->order('turn DESC')->limit(5)->select();
                break;
            case 'suiji':
                $article=$articleView->where($where)->field(array('id','title','isturn'))->limit($limit)->select();
                break;
        }
//        var_dump($article);die;
      if($article){
//           echo json_encode($article);
          $str1='';
          foreach($article as $v) {
              if ($v['isturn']) {
                  $str1 .= '<li><a href="' . U('User/article', array('id' => $v['id'])) . '" title="' . $v['title'] . '">' . $v['title'] . '(转)</a></li>';
              }else{
                  $str1 .= '<li><a href="' . U('User/article', array('id' => $v['id'])) . '" title="' . $v['title'] . '">' . $v['title'] . '</a></li>';
              }
          }
          echo $str1;
      }else{
          echo 'false';
      }
    }

}