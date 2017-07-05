<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/6 0006
 * Time: 21:34
 */

namespace Admin\Controller;

use Common\Org\Page;
use Model\ArticleViewModel;
use Common\Org\Data;
use Model\CommentViewModel;
use Think\Controller;
class ArticleController extends AdminBaseController
{
    //获取所有原创文章数据
    public function index()
    {
        $articleView = new ArticleViewModel();
        $where1 = array('isturn' => 0);
        //文章搜索
        if (IS_GET && isset($_GET['submiting'])) {
            $keyword = trim(I('get.keyword'));
            if (isset($keyword) && $keyword) {
//               p($keyword);
                $where2 = array('content' => array('like', '%' . $keyword . '%'), 'isturn' => 0);
            }
        }
        $map = $where2 ? $where2 : $where1;
        $p=I('get.p','','intval');
        $p=$p?$p:1;
        $limit=($p-1)*10;
        $limit=$limit.',10';
        $data = $articleView->getAll($map,$limit);
        $count = $articleView->where($map)->count();
        //对数据进行处理,提取图片，文章截取一段显示
        $data = Data::dealData($data);
//       p($data);
//       var_dump($data);
        //分页显示
        $page = new Page($count, 5);
        $this->assign('keyword', $keyword);
        $this->assign('data', $data ? $data : false);
        $this->assign('page', $page->show());
        $this->display();
    }

    //获取所有转发文章数据
    public function turn()
    {
        $articleView = new ArticleViewModel();
        $where1 = array('isturn' => array('neq', 0));
        if (IS_GET && isset($_GET['submiting'])) {
            $keyword = trim(I('get.keyword'));
            if (isset($keyword) && $keyword) {
//               p($keyword);
                $where2 = array('content' => array('like', '%' . $keyword . '%'), 'isturn' => 0);
            }
        }
        $map = $where2 ? $where2 : $where1;
        $p=I('get.p','','intval');
        $p=$p?$p:1;
        $limit=($p-1)*10;
        $limit=$limit.',10';
        $data = $articleView->getAll($map,$limit);
        $count = $articleView->where($map)->count();
        //对数据进行处理,提取图片，文章截取一段显示
        $data = Data::dealData($data);
//       p($data);
        //分页显示
        $page = new Page($count, 5);
        $this->assign('keyword', $keyword);
        $this->assign('data', $data);
        $this->assign('page', $page->show());
        $this->display();
    }

    //文章删除
    public function delArticle()
    {
        if (IS_GET) {
            $aid = I('get.id', '', 'intval');
            //增加签名防止用户直接在url上进行删除操作，这样就只能在document删除按钮中进行操作
            $sign = I('get.sign');
            $preg = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
            $content = M('article')->where(array('id' => $aid))->getField('content');
            $content = htmlspecialchars_decode($content);
//        var_dump($content);
            preg_match_all($preg, $content, $src);
//        $src=substr($src[1][0],9,255);
//        var_dump($src);die;
            $db = M('article');
//        echo $aid;
            if ($db->delete($aid)) {
                //删除微博中存在的图片
                if (is_array($src) && !empty($src)) {
                    foreach ($src[1] as $v) {
                        $v = substr($v, 9, 255);
                        unlink($v);
                    }
                }
                //该用户对应的发布文章数减1
                M('userinfo')->where(array('uid' => $_SESSION['uid']))->setDec('article', 1);
                //对应文章评论，收藏都应该删除
                M('comment')->where(array('aid' => $aid))->delete();
                M('collect')->where(array('aid' => $aid))->delete();
                //如果该篇文章是转发的，那么原文章转发数-1
                $isturn = $db->where(array('id' => $aid))->getField('isturn');
                if ($isturn) {
                    $db->where(array('id' => $isturn))->setDec('turn', 1);
                }
                $this->success('删除成功!', U('User/index'));
            } else {
                $this->error('删除该篇文章失败!');
            }
        }
    }

    public function comment()
    {
        //获取评论列表
        $commentView = new CommentViewModel();
        if (IS_GET && isset($_GET['submiting'])) {
            $keyword = trim(I('get.keyword'));
            if (isset($keyword) && $keyword) {
//               p($keyword);
                $where = array('content' => array('like', '%' . $keyword . '%'), 'isturn' => 0);
            }
        }
        $where = $where ? $where : '';
        $p=I('get.p','','intval');
        $p=$p?$p:1;
        $limit=($p-1)*10;
        $limit=$limit.',10';
        $data = $commentView->where($where)->limit($limit)->select();
//         var_dump($data);
        $count = $commentView->count();
        //分页显示
        $page = new Page($count, 5);
        $this->assign('keyword', $keyword);
        $this->assign('data', $data);
        $this->assign('page', $page->show());
        $this->display();
    }

    public function delComment()
    {
        if (IS_GET) {
            $id = I('get.id', '', 'intval');
            $db = M('comment');
            $where = array('id' => $id);
            //是否是主评论或者是评论的回复
            $fid = $db->where($where)->getField('fid');
            //获取该评论属于的文章
            $aid = $db->where($where)->getField('aid');
            if ($db->delete($id)) {
                //删除该评论下面的所有回复
                if ($fid == 0) {
                    $rids = $db->where(array('fid' => $id))->select();
                    if ($rids) {
                        $rids = implode(',', $rids);
                        $db->delete($rids);
                    }
                }
                $this->success('删除成功!', U('Admin/article/comment'));
            } else {
                $this->error('删除评论失败!');
            }
        }
    }
}