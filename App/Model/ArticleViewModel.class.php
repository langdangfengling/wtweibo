<?php
namespace Model;
/**
 * 读取微博视图模型
 * @author wt
 */
class ArticleViewModel extends \Think\Model\ViewModel
{
    /*
     * 定义是图标关联关系
     */
    public $viewFields = array(
        'article' => array('id', 'content', 'isturn', 'time', 'turn', 'collect', 'comment', 'gid','name'=>'title',
            '_type' => 'left' //左外连接查询,针对下个表有效
        ),
        'agroup' => array('name' => 'gname','uid','_type' => 'left','_on' => 'article.gid = agroup.id'),
        'userinfo' => array(
            'username', 'face60' => 'face', '_on' => 'agroup.uid=userinfo.uid',//连接查询条件
            '_type' => 'LEFT'
        ),
//        'picture' => array(
//            'max', 'medium', 'mini', '_on' => 'weibo.id=picture.wid'
//        ),
        //以上查询sql语句为
        /* //系统的sql语句 SELECT weibo.id AS id,weibo.content AS content,weibo.isturn AS isturn,weibo.time AS time,weibo.turn AS turn,weibo.collect AS collect,
        * weibo.comment AS comment,weibo.uid AS uid,userinfo.username AS username,userinfo.face50 AS face,picture.thumb800 AS thumb800,
        * picture.thumb380 AS thumb380,picture.thumb120 AS thumb120 FROM wt_weibo weibo LEFT JOIN wt_userinfo userinfo
        *  ON weibo.uid=userinfo.uid LEFT JOIN wt_picture picture ON weibo.id=picture.wid WHERE weibo.uid IN ('2','6','5','4') ORDER BY weibo.time DESC LIMIT 0,3
        */
    );

    /*
     * 返回查询所有记录
     */
    public function getAll($where, $limit)
    {
        $result = $this->where($where)->order('time DESC')->limit($limit)->select();
//        foreach ($result as $k => $v) {
//            if ($v['isturn']) {
//                //取得转发微博所有内容
//                $turn = $this->where(array('id' => $v['isturn']))->find();
//                //重组结果将原微博内容添加到转发微博中
//                $result[$k]['isturn'] = isset($turn)?$turn:-1;
//            }
//        }
        return $result;
    }
}