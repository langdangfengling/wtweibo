<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/20 0020
 * Time: 20:52
 */

namespace Home\Controller;
use Model\GuestViewModel;
use Model\ArticleViewModel;
use Model\CommentViewModel;
use Think\Controller;
use Think\Page;
class AccountController extends CommonController
{
    /**
     * 用户主页
     */
    public function index(){
        //进入其他用户主页
//        if(isset($_GET['uid']) && $_GET['uid'] != session('uid')) {
//            $uid = I('get.uid', '', 'intval');
//            session('account_id',$uid);
//            $this->display('Account/index');
//        }
        //存入session中，在right.html中统一获取用户数据，避免其他页面无法获得用户信息
        if(!IS_GET){
            $this->error('非法请求');
        }
        $account_id=I('get.user_id','','intval');
        session('account_id',$account_id);//这里如果打开多个用户主页会导致前后覆盖 待解决
        //这里将访问者写入库
        $visitor=session('uid');
//        var_dump($visitor);die;
        $this->recordVisitor($visitor,$account_id);
        //读取用户以及该用户关注的用户的文章  这里按想法只读取用户的文章就行
//        $uids=array($account_id);
//        $where=array('fans'=>$account_id);
//        if($result=M('follow')->where($where)->field('follow')->select()){
////                  dump($result);die;
//            //将我关注用户的id加入到$uids数组中
//            foreach($result as $v){
//                $uids[]=$v['follow'];
//            }
//        }
//       dump($uids);die;
        //调用微博视图模型得到所有数据
        $where=array(
            'uid'=>$account_id,
        );
        $articleView=new ArticleViewModel();
        //分页显示
        $count=$articleView->where($where)->count();
        $page=new Page($count,5);
        $limit=$page->firstRow.','.$page->listRows;
        $article=$articleView->getAll($where,$limit);
//        //只显示自己发布的文章
//        if($_GET['uid'] && $uid=I('get.uid','','intval')){
//            $where=array('uid' => $uid);
//            //分页显示
//            $count=$articleView->where($where)->count();
//            $page=new Page($count,5);
//            $limit=$page->firstRow.','.$page->listRows;
//            $article=$articleView->getAll($where,$limit);
//        }
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
    //全文读取文章
    //全文读取文章.并且读取该文章下的所有有关数据
    public function article(){
        if(!IS_GET){
            $this->error('非法请求');
        }
        //文章
        $id=I('get.id','','intval');
        //这里将访问者写入库
        $visitor=session('uid');
        $data=array(
            'visitor' => $visitor,
            'time' => time(),
            'uid' => session('account_id'),
            'aid' => $id,
        );
        //自己访问不计入内
        if($visitor != session('account_id')){
            $db=M('visitors');
            $where=array('visitor' => $visitor,'aid' => $id);
            //如果保存的最近访问者已经存在，则更新数据
            $old_visitor=$db->where($where)->find();
            if($old_visitor){
                $db->where($where)->save($data);
            }else{
                $db->data($data)->add();
            }
        }
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
//        var_dump($commentcount);die;
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
    /**
     * 个人档
     */
    public function about(){
        $this->display();
    }
    /**
     * 说说
     */
    public function talk(){
        $this->display();
    }
    /**
     * 相册
     */
    public function album(){
        $db=M('album');
        $count=$db->where(array('uid' => session('account_id')))->count();
        $page=new Page($count,12);
        $limit=$page->firstRow.','.$page->listRows;
        $album=$db->where(array('uid' => session('account_id')))->order('time DESC')->limit($limit)->select();
        //分页自定义样式
        $page->lastSuffix=false;//最后一页是否显示总页数
        $page->rollPage=4;//分页栏每页显示的页数
        $page->setConfig('prev','【上一页】');
        $page->setConfig('next','【下一页】');
        $page->setConfig('first','【首页】');
        $page->setConfig('last','【末页】');
        $page->setConfig('theme','共%TOTAL_ROW%条记录，当前是%NOW_PAGE%/%TOTAL_PAGE% %FIRST% %UP_PAGE% %DOWN_PAGE% %END%');
        $this->album=$album?$album:false;
        $this->page=$page->show();
        $this->display();
    }
    /**
     * 照片
     */
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
    /**
     * 用户留言处理，
     */
    public function guest()
    {
        /**
         * 读取该用户留言数据，展示在模板中
         */
        $where = array('uid' => session('account_id'));
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
            'uid' => session('account_id'),//留言所属用户
        );
        //        //读取推送消息
        set_msg(session('account_id'),4);
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