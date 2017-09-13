<?php
/**
 * 数据处理类
 */
namespace Common\Org;
final class Data
{
    /**
     * 对文章内容进行处理，提取文章中的图片路径，以及内容反转义并截取一段显示
     * @param  array $data
     * @return array
     */
    static public function dealData($data){
        //    匹配文章内容中图片的src正则表达式
//        $preg='<img[\s]+src[\s]*=[\s]*(([\'\"](?<src>[^\'\"]*)[\'\"])|(?<src>[^\s]*))';//不行
//        $preg='/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i'; //OK 不懂怎么可以匹配 ？？？？？？？？？？/这个可以找到文章所有的图片标签，单全部集合在一个字符串中，不好提取地址
        $preg="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
        //.*连在一起就意味着任意数量的不包含换行的字符。现在\bhi\b.*\bLucy\b的意思就很明显了：先是一个单词hi,然后是任意个任意字符(但不能是换行)，最后是Lucy这个单词。
//                需对文章内容稍作处理，存在数据库中的数据已经被转义，所以需要反转义回来,然后在截取一段字作文文章的描述
        if($data) {
            foreach ($data as $k => $v) {
                $data[$k]['content'] = htmlspecialchars_decode($v['content']);//反转义
                $data[$k]['content']=stripslashes($data[$k]['content']);//将字符串进行处理,去掉反斜杠
                //如果文章内容中存在图片，文章中图片的路径src
                preg_match_all($preg,$data[$k]['content'],$src);
//                var_dump($src);
                $data[$k]['src']=$src[1];
                $data[$k]['content'] =strip_tags($data[$k]['content']);//去除字符串中html和php标签
                $data[$k]['content']=str_replace("/","",$data[$k]['content']);//去除反斜杠
                $data[$k]['content'] = substr($data[$k]['content'], 0, 360);
                //文章评论数
                $data[$k]['commentcount']=M('comment')->where(array('fid' => 0,'aid' =>$v['id']))->count();
            }
        }
        return $data;
    }
    /**
     * 返回多层栏目
     * @param $data 操作的数组
     * @param int $pid 一级PID的值
     * @param string $html 栏目名称前缀
     * @param string $fieldPri 唯一键名，如果是表则是表的主键
     * @param string $fieldPid 父ID键名
     * @param int $level 不需要传参数（执行时调用）
     * @return array
     */
    static public function channelLevel($data, $pid = 0, $html = "&nbsp;", $fieldPri = 'cid', $fieldPid = 'pid', $level = 1)
    {
        if (empty($data)) {
            return array();
        }
        $arr = array();
        foreach ($data as $v) {
            if ($v[$fieldPid] == $pid) {
                $arr[$v[$fieldPri]] = $v;
                $arr[$v[$fieldPri]]['_level'] = $level;
                $arr[$v[$fieldPri]]['_html'] = str_repeat($html, $level - 1);
                $arr[$v[$fieldPri]]["_data"] = self::channelLevel($data, $v[$fieldPri], $html, $fieldPri, $fieldPid, $level + 1);
            }
        }
        return $arr;
    }

    /**
     * 获得所有子栏目
     * @param $data 栏目数据
     * @param int $pid 操作的栏目
     * @param string $html 栏目名前字符
     * @param string $fieldPri 表主键
     * @param string $fieldPid 父id
     * @param int $level 等级
     * @return array
     */
    static public function channelList($data, $pid = 0, $html = "&nbsp;", $fieldPri = 'cid', $fieldPid = 'pid', $level = 1)
    {
        $data = self::_channelList($data, $pid, $html, $fieldPri, $fieldPid, $level);
        if (empty($data))
            return $data;
        foreach ($data as $n => $m) {
            if ($m['_level'] == 1)
                continue;
            $data[$n]['_first'] = false;
            $data[$n]['_end'] = false;
            if (!isset($data[$n - 1]) || $data[$n - 1]['_level'] != $m['_level']) {
                $data[$n]['_first'] = true;
            }
            if (isset($data[$n + 1]) && $data[$n]['_level'] > $data[$n + 1]['_level']) {
                $data[$n]['_end'] = true;
            }
        }
        //更新key为栏目主键
        $category=array();
        foreach($data as $d){
            $category[$d[$fieldPri]]=$d;
        }
        return $category;
    }

    //只供channelList方法使用
    static private function _channelList($data, $pid = 0, $html = "&nbsp;", $fieldPri = 'cid', $fieldPid = 'pid', $level = 1)
    {
        if (empty($data))
            return array();
        $arr = array();
        foreach ($data as $v) {
            $id = $v[$fieldPri];
            if ($v[$fieldPid] == $pid) {
                $v['_level'] = $level;
                $v['_html'] = str_repeat($html, $level - 1);
                array_push($arr, $v);
                $tmp = self::_channelList($data, $id, $html, $fieldPri, $fieldPid, $level + 1);
                $arr = array_merge($arr, $tmp);
            }
        }
        return $arr;
    }

    /**
     * 获得树状数据
     * @param $data 数据
     * @param $title 字段名
     * @param string $fieldPri 主键id
     * @param string $fieldPid 父id
     * @return array
     */
    static public function tree($data, $title, $fieldPri = 'id', $fieldPid = 'pid')
    {
        if (!is_array($data) || empty($data))
            return array();
        $arr = Data::channelList($data, 0, '', $fieldPri, $fieldPid);
        foreach ($arr as $k => $v) {
            $str = "";
            if ($v['_level'] > 2) {
                for ($i = 1; $i < $v['_level'] - 1; $i++) {
                    $str .= "&emsp;│";
                }
            }
            if ($v['_level'] != 1) {
                $t = $title ? $v[$title] : "";
                if (isset($arr[$k + 1]) && $arr[$k + 1]['_level'] >= $arr[$k]['_level']) {
                    $arr[$k]['_name'] = $str . "&emsp;├─ " . $v['_html'] . $t;
                } else {
                    $arr[$k]['_name'] = $str . "&emsp;└─ " . $v['_html'] . $t;
                }
            } else {
                $arr[$k]['_name'] = $v[$title];
            }
        }
        //设置主键为$fieldPri
        $data = array();
        foreach ($arr as $d) {
            $data[$d[$fieldPri]] = $d;
        }
        return $data;
    }

    /**
     * 获得所有父级栏目
     * @param $data 栏目数据
     * @param $sid 子栏目
     * @param string $fieldPri 唯一键名，如果是表则是表的主键
     * @param string $fieldPid 父ID键名
     * @return array
     */
    static public function parentChannel($data, $sid, $fieldPri = 'cid', $fieldPid = 'pid')
    {
        if (empty($data)) {
            return $data;
        } else {
            $arr = array();
            foreach ($data as $v) {
                if ($v[$fieldPri] == $sid) {
                    $arr[] = $v;
                    $_n = self::parentChannel($data, $v[$fieldPid], $fieldPri, $fieldPid);
                    if (!empty($_n)) {
                        $arr = array_merge($arr, $_n);
                    }
                }
            }
            return $arr;
        }
    }

    /**
     * 判断$s_cid是否是$d_cid的子栏目
     * @param $data 栏目数据
     * @param $sid 子栏目id
     * @param $pid 父栏目id
     * @param string $fieldPri 主键
     * @param string $fieldPid 父id字段
     * @return bool
     */
    static function isChild($data, $sid, $pid, $fieldPri = 'cid', $fieldPid = 'pid')
    {
        $_data = self::channelList($data, $pid, '', $fieldPri, $fieldPid);
        foreach ($_data as $c) {
            //目标栏目为源栏目的子栏目
            if ($c[$fieldPri] == $sid)
                return true;
        }
        return false;
    }

    /**
     * 检测是不否有子栏目
     * @param $data 栏目数据
     * @param $cid 要判断的栏目cid
     * @param string $fieldPid 父id表字段名
     * @return bool
     */
    static function hasChild($data, $cid, $fieldPid = 'pid')
    {
        foreach ($data as $d) {
            if ($d[$fieldPid] == $cid) return true;
        }
        return false;
    }

    /**
     * 递归实现迪卡尔乘积
     * @param $arr 操作的数组
     * @param array $tmp
     * @return array
     */
    static function descarte($arr, $tmp = array())
    {
        static $n_arr = array();
        foreach (array_shift($arr) as $v) {
            $tmp[] = $v;
            if ($arr) {
                self::descarte($arr, $tmp);
            } else {
                $n_arr[] = $tmp;
            }
            array_pop($tmp);
        }
        return $n_arr;
    }

}
