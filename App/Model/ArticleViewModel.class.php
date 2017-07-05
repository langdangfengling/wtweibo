<?php
namespace Model;
use Think\Model\ViewModel;
/**
 * 读取微博视图模型
 * @author wt
 */
class ArticleViewModel extends ViewModel
{
    /*
     * 定义是图标关联关系
     */
    public $viewFields = array(
        'article' => array('id', 'content', 'isturn', 'time', 'turn', 'collect', 'readcount', 'comment', 'gid','name'=>'title',//read是关键字
            '_type' => 'left' //左外连接查询,针对下个表有效
        ),
        'agroup' => array('name' => 'gname','uid','_type' => 'left','_on' => 'article.gid = agroup.id'),
        'userinfo' => array(
            'username', 'face60' => 'face', '_on' => 'agroup.uid=userinfo.uid',//连接查询条件
            '_type' => 'LEFT'
        ),
    );

    /*
     * 返回查询所有记录
     */
    public function getAll($where='', $limit='')
    {
        $result = $this->where($where)->order('time DESC')->limit($limit)->select();
        foreach ($result as $k => $v) {
            if ($v['isturn']) {
                //取得转发微博所有内容
                $turn = $this->where(array('id' => $v['isturn']))->field(array('username','turn','id','uid'))->find();
                //重组结果将原微博内容添加到转发微博中
                $result[$k]['isturn'] = isset($turn)?$turn:-1;
            }
        }
        return $result;
    }
    /*
    * 返回单条记录，用于文章阅读
    */
    public function getOne($where){
        $result = $this->where($where)->find();
        if($result) {
            if ($result['isturn']) {
                //取得转发微博所有内容
                $turn = $this->where(array('id' => $result['isturn']))->field(array('username', 'turn', 'id', 'uid'))->find();
                //重组结果将原微博内容添加到转发微博中
                $result['isturn'] = isset($turn) ? $turn : -1;
            }
        }
        return $result;
    }
}