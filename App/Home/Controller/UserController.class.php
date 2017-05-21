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
use Model\CommentViewModel;
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
        //将我和我关注的用户所有的文章数据都得到，输出到模板中
        $uids=array(session('uid'));
        $where=array('fans'=>session('uid'));
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
        $count=$articleView->where($where)->count();
        $page=new Page($count,3);
        $limit=$page->firstRow.','.$page->listRows;
        $article=$articleView->getAll($where,$limit);

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
                $count=$articleView->where($where)->count();
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
        // dump($article);
        //    匹配文章内容中图片的src正则表达式
//        $preg='<img[\s]+src[\s]*=[\s]*(([\'\"](?<src>[^\'\"]*)[\'\"])|(?<src>[^\s]*))';//不行
//        $preg='/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i'; //OK 不懂怎么可以匹配 ？？？？？？？？？？/这个可以找到文章所有的图片标签，单全部集合在一个字符串中，不好提取地址
        $preg="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
        //.*连在一起就意味着任意数量的不包含换行的字符。现在\bhi\b.*\bLucy\b的意思就很明显了：先是一个单词hi,然后是任意个任意字符(但不能是换行)，最后是Lucy这个单词。
//                需对文章内容稍作处理，存在数据库中的数据已经被转义，所以需要反转义回来,然后在截取一段字作文文章的描述
        if($article) {
            foreach ($article as $k => $v) {
                $article[$k]['content'] = htmlspecialchars_decode($v['content']);//反转义
                //如果文章内容中存在图片，文章中图片的路径src
                preg_match_all($preg,$article[$k]['content'],$src);
//                var_dump($src);
                $article[$k]['src']=$src[1];
                $article[$k]['content'] = strip_tags($article[$k]['content']);//去除字符串中html和php标签
                $article[$k]['content'] = substr($article[$k]['content'], 0, 360);
                //文章评论数
                $article[$k]['commentcount']=M('comment')->where(array('fid' => 0,'aid' =>$v['id']))->count();
            }
        }
//                   dump($article);  die;
        //分页自定义样式
        $page->lastSuffix=false;//最后一页是否显示总页数
        $page->rollPage=4;//分页栏每页显示的页数
        $page->setConfig('prev','【上一页】');
        $page->setConfig('next','【下一页】');
        $page->setConfig('first','【首页】');
        $page->setConfig('last','【末页】');
        $page->setConfig('theme','共%TOTAL_ROW%条记录，当前是%NOW_PAGE%/%TOTAL_PAGE% %FIRST% %UP_PAGE% %DOWN_PAGE% %END%');
        $this->article=$article?$article:false;
        $this->page=$page->show();
        $this->display();
    }
//文章发布入库
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
            $this->success('发布成功',U('User/index'));
        }else{
            $this->error('发布失败');
        }
    }
    //全文读取文章.并且读取该文章下的所有有关数据
    public function article(){
        if(!IS_GET){
            $this->error('非法请求');
        }
        //文章
        $id=I('get.id','','intval');
        //给该文章阅读数+1
        M('article')->where(array('id' => $id))->setInc('readcount',1);
        $ArticleView=new ArticleViewModel();
        $where=array('id' =>$id);
        $article=$ArticleView->getOne($where);
//        var_dump($article);die;
        if(!$article){
            $this->error('没有找到相关页面');
            return false;
        }
        //评论数据
        $where=(array('aid' => $id,'fid' => 0));//这里一定要带上条件fid=0，然后commentview类里对评论重新组合
        $commentView=new CommentViewModel();
        $commentcount=$commentView->where($where)->count();//这里的comment不要用文章表中的comment数，那个是将回复也算进去了
        //导入第三方分页显示类库
        import("Common.Org.PageAjax");
        $Page = new \PageAjax($commentcount,5,1,2);//这里文章页显示的评论数据是自然加载，默认显示第一页数据，此时的都不带a连接，这里如果想异步获取数据，那么就需要再写一个方法来异步获取评论，并传递page（当前页）参数
//        dump($count);die;
        $limit=5;
        $comments=$commentView->getAll($where,$limit);
        $this->commentcount=$commentcount;
        $this->article=$article;
        $this->comments=$comments?$comments:0;
        $this->assign('page',$Page->myde_write());
        $this->display();
    }
    //文章评论异步提交
    public function sendComment()
    {
        if (!IS_AJAX) {
            $this->error('非法提交');
        }
        $fid = I('post.fid', '', 'intval');
        $content = I('post.content');
//            if (time() - session("comment_time") < 60 && session("comment_time") > 0) {//2分钟以后发布
//                echo json_encode(array("status" => -2, "error" => "您提交评论的速度太快了，请稍后再发表评论。"));
//                exit;
//        }
        $data = array(
            'aid' => I('post.aid', '', 'intval'),
            'content' => $content,
            'time' => time(),
            'uid' => session('uid'),
            'fid' => $fid
        );
        $id = M('comment')->data($data)->add();
        if ($id) {
            //读取评论用户信息
            $field = array('username', 'face60' => 'face');
            $where = array('uid' => $data['uid']);
            $user = M('userinfo')->where($where)->field($field)->find();
//            //文章的发布者用户信息
//            $uid=I('post.uid','','intval');
//            $where=array('uid'=>$uid);
//            $username=M('userinfo')->where($where)->getField('username');


//            //推送消息
//            set_msg($uid,1);
//            //评论同时转发时处理
//            $isturn=I('post.isturn','','intval');
////            dump($isturn);die;
//            if($isturn){
//                //读取转发微博内容与id
//                $field=array('id','content','isturn');
//                $weibo=$db->field($field)->find($data['wid']);
//                $tid=$weibo['isturn']?$weibo['isturn']:$weibo['id'];
//                $content=$weibo['isturn']?$data['content'].'@//'.$username.':'.$weibo['content']:$data['content'];//如果要转发的微博不是原微博，那么内容就是我评论的内容加上要转发微博的内容，否则只是我评论的内容
//                //将我转发的微博以及评论的内容存入到数据库中
//                $cons=array(
//                    'isturn' =>$tid,
//                    'uid' =>$data['uid'],
//                    'content' =>$content,
//                    'time' => $data['time'],
//                );
//                if($db->add($cons)){
//                    //微博转发数加+1
//                    $db->where(array('id'=>$weibo['id']))->setInc('turn',1);
//                }
//                echo 1;
//                die;//这里如果评论同时转发的话就不用异步显示评论，需要将页面刷新重新载入转发后的微博
//            }

            if (!$fid) {//评论字符串
                $str = '';
                $str .= '<li class="comment_list clearfix"><div class="comment_avatar">';
                $str .= '<span class="userPic"><img width="36" height="36" src="';
                $str .= __ROOT__;
                if ($user['face']) {
                    $str .= '/' . $user['face'];
                } else {
                    $str .= '/Public/Images/noface.gif';
                }
                $str .= '" alt="' . $user['username'] . '" width="36" height="36" /></span>';
                $str .= ' <span class="grey">';
                if (session('uid') == $data['uid']) {
                    $str .= '我';
                } else {
                    $str .= $user['username'];
                }
                $str .= '</span></div>';
                $str .= '<div class="comment_conBox"><div class="comment_avatar_time">';
                $str .= '<div class="time">' . time_format($data["time"]) . '</div>';
                $str .= $id . '楼';
                $str .= '</div><div class="comment_conWrap clearfix">';
                $str .= '<div class="comment_action"><a class="reply" fid="' . $id . '">回复</a> </div>';
                $str .= '<div class="comment_con">' . $data['content'] . '</div>';
                $str .= '</div></div></li>';
                echo $str;
            } else {
                //该文章评论数加+1 是评论的+1，回复的不算
                M('article')->where(array('id' => $data['aid']))->setInc('comment', 1);
                //评论回复字符串
                $str = '';
                $str .= '<blockquote class="comment_blockquote"><div class="comment_floor" >';
                $str .= time_format($data["time"]) . '</div>';
                $str .= '<div class="comment_conWrap clearfix" ><div class="comment_con" >';
                $str .= $user['username'] . ':';
                $str .= '<p>' . $data["content"] . '</p></div>';
                $str .= '<div class="comment_action_sub" > <a class="reply" > 回复</a ></div ></div>';
                $str .= '<div fid = "' . $fid . '" class="reply_area_sub" >';
                $str .= ' <textarea class="textarea_comment" autocomplete = "off" name = "content" ></textarea >';
                $str .= '<div class="btn_p clearfix" >';
                $str .= '<span class="comment_tip"></span>';
                $str .= ' <button class="btn_subGrey btn" type = "button" > 提交</button >';
                $str .= '<ul class=\'fleft\' ><li title = \'表情\' ><i class=\'icon icon-phiz phiz\' sign = \'comment\' ></i ></li ></ul >';
                $str .= '  </div ></div ></blockquote >';
                echo $str;
            }
        } else {
            echo 'false';
        }
    }
 //文章异步收藏
  public function keep(){
      if(!IS_AJAX){
          $this->error('非法请求');
      }
      $aid=I('post.aid','','intval');
      $data=array('aid' => $aid,'time' => time(),'uid' => session('uid'));
      //如果已收藏直接返回
      $db=M('collect');
      $where=array('aid' => $aid,'uid' => session('uid'));
      $id=$db->where($where)->getField('id');
      if($id){
          echo json_encode(array('status' => -1,'msg' => '您已收藏过该篇文章!'));
          return false;
      }
      if($db->data($data)->add()){
          //该微博数收藏+1
          M('article')->where(array('aid' => $id))->setInc('collect',1);
          echo json_encode(array('status' => 1,'msg' => '恭喜！收藏成功'));
      }else{
          echo json_encode(array('status' => 0, 'msg' => '收藏失败'));
      }
  }
    //文章删除
    public function delarticle()
    {
        if (!IS_GET) {
            $this->error('非法提交');
        }
        $aid = I('get.aid', '', 'intval');
        //增加签名防止用户直接在url上进行删除操作，这样就只能在document删除按钮中进行操作
        $sign = I('get.sign');
        $preg="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
        $content=M('article')->where(array('id' => $aid))->getField('content');
        $content=htmlspecialchars_decode($content);
//        var_dump($content);
        preg_match_all($preg,$content,$src);
//        $src=substr($src[1][0],9,255);
//        var_dump($src);die;
        $db=M('article');
        if ($sign == md5($aid)) {
//        echo $aid;
            if ($db->delete($aid)) {
                //删除微博中存在的图片
                if(is_array($src) && !empty($src)){
                    foreach($src[1] as $v){
                        $v=substr($v,9,255);
                            unlink($v);
                        }
                    }
                //该用户对应的发布文章数减1
                M('userinfo')->where(array('uid' => $_SESSION['uid']))->setDec('article', 1);
                //对应文章评论，收藏都应该删除
                M('comment')->where(array('aid' => $aid))->delete();
                M('collect')->where(array('aid' => $aid))->delete();
                //如果该篇文章是转发的，那么原文章转发数-1
                $isturn=$db->where(array('id' => $aid))->getField('isturn');
                if($isturn){
                    $db->where(array('id' => $isturn))->setDec('turn',1);
                }
                $this->success('删除成功!', U('User/index'));
            } else {
                $this->error('删除该篇文章失败!');
            }
        }else{
            $this->error('非法操作');
        }
    }
    //文章类别修改
    public function alterAroup(){
        if(!IS_POST){
            $this->error('非法请求');
        }
//        var_dump($_POST);die;
        $aid=I('post.aid','','intval');
        $gid=I('post.gid','','intval');
        if(M('article')->where(array('id' => $aid))->setField('gid',$gid)){
            $this->success('修改成功',$_SERVER['HTTP_REFERER']);
        }else{
            $this->error('修改失败');
        }
    }
    //文章转发
    public function turn(){
        if(!IS_AJAX){
            $this->error('非法操作');
        }
        $aid=I('post.aid','','intval');
        //获取原微博数据
        $db=M('article');
        $article=$db->where(array('id' => $aid))->find();
        //获取数据
        $data=array(
            'isturn' => $aid,
            'content' => $article['content'],
            'turn' =>$article['turn'],//转发的次数继承自原文章的次数，收藏和评论就不用
            'time' => time(),
            'gid' => I('post.gid','','intval'),
            'name' => $article['name'],
        );
        if($db->data($data)->add()){
            //该用户发布文章+1
            M('userinfo')->where(array('uid' => session('uid')))->setInc('article',1);
            //该文章转发数+1
            $db->where(array('id' => $aid))->setInc('turn',1);
            echo 1;
        }else{
            echo 0;
        }
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
        if(!$album1){
            $this->error('你要找得页面不存在');
            return false;
        }
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