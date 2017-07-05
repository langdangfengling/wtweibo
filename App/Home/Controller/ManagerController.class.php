<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/21 0021
 * Time: 14:13
 */

namespace Home\Controller;
use Model\CommentViewModel;
use Model\KeepViewModel;
use Model\ArticleViewModel;
use Model\LetterViewModel;
use Think\Controller;
use Think\Page;
class ManagerController extends CommonController
{
   /**
    * 收藏
    * 对文章的操作就是取消收藏、评论、转发等功能
    */
    public function keep(){
    //读取收藏的文章数据
//        $ids=M('collect')->where(array('uid' => session('uid')))->getField('id',true);
//        var_dump($ids);die;
//        if($ids) {
//            $ids = implode(',', $ids);
//            $where = array(
//                'kid' => array('in', $ids),
//            );
           $where=array('keep_uid' => session('uid'));
//        var_dump(session('uid'));
            $keepView = new KeepViewModel();
            //分页显示
            $count = $keepView->where($where)->count();
            $page = new Page($count, 5);
            $limit = $page->firstRow . ',' . $page->listRows;
            $article = $keepView->getAll($where, $limit);
//            var_dump($article);die;
            //    匹配文章内容中图片的src正则表达式
//        $preg='<img[\s]+src[\s]*=[\s]*(([\'\"](?<src>[^\'\"]*)[\'\"])|(?<src>[^\s]*))';//不行
//        $preg='/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i'; //OK 不懂怎么可以匹配 ？？？？？？？？？？/这个可以找到文章所有的图片标签，单全部集合在一个字符串中，不好提取地址
            $preg = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
            //.*连在一起就意味着任意数量的不包含换行的字符。现在\bhi\b.*\bLucy\b的意思就很明显了：先是一个单词hi,然后是任意个任意字符(但不能是换行)，最后是Lucy这个单词。
//                需对文章内容稍作处理，存在数据库中的数据已经被转义，所以需要反转义回来,然后在截取一段字作文文章的描述
            if ($article) {
                foreach ($article as $k => $v) {
                    $article[$k]['content'] = htmlspecialchars_decode($v['content']);//反转义
                    //如果文章内容中存在图片，文章中图片的路径src
                    preg_match_all($preg, $article[$k]['content'], $src);
//                var_dump($src);
                    $article[$k]['src'] = $src[1];
                    $article[$k]['content'] = strip_tags($article[$k]['content']);//去除字符串中html和php标签
                    $article[$k]['content'] = substr($article[$k]['content'], 0, 360);
                    //文章评论数
                    $article[$k]['commentcount'] = M('comment')->where(array('fid' => 0, 'aid' => $v['id']))->count();
                }
            }
            //分页自定义样式
            $page->lastSuffix = false;//最后一页是否显示总页数
            $page->rollPage = 4;//分页栏每页显示的页数
            $page->setConfig('prev', '【上一页】');
            $page->setConfig('next', '【下一页】');
            $page->setConfig('first', '【首页】');
            $page->setConfig('last', '【末页】');
            $page->setConfig('theme', '共%TOTAL_ROW%条记录，当前是%NOW_PAGE%/%TOTAL_PAGE% %FIRST% %UP_PAGE% %DOWN_PAGE% %END%');
            $this->assign('page',$page->show());

        $article=$article?$article:false;
        $this->assign('article',$article);
        $this->assign('count',$count);
        $this->display('articleList');
    }
  //异步取消收藏
    public function keepCancel(){
        if(!IS_AJAX){
            $this->error("非法请求");
        }
        $kid=I('post.kid','','intval');
        $aid=I('post.aid','','intval');
        if(M('collect')->delete($kid)){
            //该篇文章的收藏数-1
            M('article')->where(array('id' => $aid))->setDec('collect');
            echo 1;
        }else{
            echo 0;
        }
    }
    /**
     * 评论
     */
    public function comment(){
        //获取该用户文章所有的评论，并可以对评论进行删除，回复,自己的评论不显示在内
          //1.获取改用户发布的文章id
        $uid=session('uid');
        //        //读取推送消息
        set_msg($uid,1,true);
        $where=array('uid' => $uid,'cid' => array('NEQ',$uid));
        $commentView=new CommentViewModel();
        $count=$commentView->where($where)->count();
        $page=new Page($count,15);
        $limit=$page->firstRow.','.$page->listRows;
        $comments=$commentView->where($where)->order('time DESC')->limit($limit)->select();

//        var_dump($comments);die;
        //分页自定义样式
        $page->lastSuffix = false;//最后一页是否显示总页数
        $page->rollPage = 4;//分页栏每页显示的页数
        $page->setConfig('prev', '【上一页】');
        $page->setConfig('next', '【下一页】');
        $page->setConfig('first', '【首页】');
        $page->setConfig('last', '【末页】');
        $page->setConfig('theme', '共%TOTAL_ROW%条记录，当前是%NOW_PAGE%/%TOTAL_PAGE% %FIRST% %UP_PAGE% %DOWN_PAGE% %END%');
        $this->assign('page',$page->show());
        $this->comments=$comments?$comments:false;
        $this->assign('count',$count);
         $this->display();
    }
    //评论回复
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
            echo -1;
        }else{
            echo 'false';
        }
    }
    //异步删除评论
    public function delComment(){
     if(!IS_AJAX){
         $this->error('非法请求');
     }
        $id=I('post.id','','intval');
        $fid=I('post.fid',',','intval');
        $db=M('comment');
        //如果删除的是评论，那么就需要删除该评论下面所有的回复
        if($db->delete($id)){
            if($fid == 0){
                $db->where(array('fid' => $id))->delete();
            }
            echo 1;
        }else{
            echo 0;
        }
    }

    /**
     * @提到我的文章展示
     */
    public function atme()
    {
//        //读取推送消息
        set_msg(session('uid'),3,true);
        //从表中读取数据
        $where = array('uid' => session('uid'));
        $aids = M('atme')->where($where)->getField('aid', true);
        $this->atme=$aids;
        $count=count($aids);
        $page=new Page($count,5);
        $limit=$page->firstRow.','.$page->listRows;
//        dump($wids);die;
        if($aids){
            $articleView=new ArticleViewModel();
            $where=array('id' =>array('in',$aids));
            $article=$articleView->getAll($where,$limit);
//            dump($weibo);die;
            $preg = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
            //.*连在一起就意味着任意数量的不包含换行的字符。现在\bhi\b.*\bLucy\b的意思就很明显了：先是一个单词hi,然后是任意个任意字符(但不能是换行)，最后是Lucy这个单词。
//                需对文章内容稍作处理，存在数据库中的数据已经被转义，所以需要反转义回来,然后在截取一段字作文文章的描述
                foreach ($article as $k => $v) {
                    $article[$k]['content'] = htmlspecialchars_decode($v['content']);//反转义
                    //如果文章内容中存在图片，文章中图片的路径src
                    preg_match_all($preg, $article[$k]['content'], $src);
//                var_dump($src);
                    $article[$k]['src'] = $src[1];
                    $article[$k]['content'] = strip_tags($article[$k]['content']);//去除字符串中html和php标签
                    $article[$k]['content'] = substr($article[$k]['content'], 0, 360);
                    //文章评论数
                    $article[$k]['commentcount'] = M('comment')->where(array('fid' => 0, 'aid' => $v['id']))->count();
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
        $this->page=$page->show();

        //展示视图
        $this->assign('count',$count);
        $this->article=$article?$article:false;
        $this->display('articleList');
    }

    /**
     * 用户私信
     */
    public function letter(){
        //读取推送消息
        set_msg(session('uid'),2,true);
        //读取私信
        $where=array('uid' => session('uid'));
        $count=M('letter')->where($where)->count();
        $page=new Page($count,10);
        $limit=$page->firstRow.','.$page->listRows;
        $letterView=new LetterViewModel();
        $letters=$letterView->getAll($where,$limit);
//        dump($letters);die;
        //展示私信页面
        $this->letter=$letters;
        $this->page=$page->show();
        $this->count=$count;
        $this->display('letter');
    }

    /**
     * 私信发送处理
     */
    public function letterSend(){
        //接收数据
        $uname=I('post.name');
        //通过用户呢城找到对应的用户Id
        $uid=M('userinfo')->where(array('username' => $uname))->getField('uid');
        if(!$uid) {
            $this->error('该用户不存在，请重试....');
        }
        $data=array(
            'from' => session('uid'),
            'time' => time(),
            'content' =>I('post.content'),
            'uid' =>$uid,
        );
        if (M('letter')->data($data)->add()) {
            //推送消息
            set_msg($uid,2);
            $this->success('发送成功', U('User/index'));
        } else{
            $this->error('发送失败，请重试.....!');
        }
    }

    /**
     * 用户私信异步删除处理
     */
    public function delLetter(){
        $lid=I('post.lid','','intval');
        if(M('letter')->delete($lid)){
            echo 1;
        }else{
            echo 0;
        }
    }

    /**
     * 空操作
     */
    public function _empty($name){
        $this->_getUrl($name);
    }
    /**
     * 处理用户名空操作，获得用户id，跳转至用户个人页
     */
    private function _getUrl($name){
        $name=htmlspecialchars($name);//转义
        $where=array('username' => $name);
        $uid=M('userinfo')->where($where)->getField('uid');
        if(!$uid){
            redirect(U('User/index'));
        }else{
            redirect(U('/Home/'.$uid));
        }
    }
    /**
     * 好友关注与粉丝管理
     */

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
}