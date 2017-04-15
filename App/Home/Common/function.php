<?php
/**
 * 公共函数
 */
/**
 * cookie加密函数，0：加密，1:解密
 * @accept string $data
 * @accept integer $type
 */
function encryption($data,$type=0){
    $key=md5(C('ENCRYPTION_KEY'));
    //加密
    if(!$type){
        return str_replace('=','',base64_encode($data ^ $key));
    }
    $data=base64_decode($data);
    return $data ^ $key;
}

/**
 * 格式化时间
 * @param $time 要格式化的时间戳
 * @return 已格式化的时间返回
 */
function time_format($time){
    //当前时间
    $now =time();
    //今天零时零分零秒的时间戳
    $today=  strtotime(date('y-m-d',$now));
//    echo date('Y-m-d H:i:s',$today);
    //传递时间与当前时间的相关秒数
    $diff=$now-$time;
    $str='';
    switch($time){
        case $diff<60:
            $str=$diff.'秒前';
            break;
        case $diff<3600:
            $str=floor($diff/60).'分钟前';
            break;
        case $diff<(3600*8);
            $str=floor($diff/3600).'小时前';
            break;
        case $time>$today:
            $str='今天&nbsp;'.date('H:i',$time);
            break;
        default:
            $str=date('Y-m-d H:i:s',$time);
            break;
    }
    return $str;
}

/**
 * 替换微博内容的URL地址、@用户与表情
 * @param $content
 * @return mix
 */
function replace_weibo($content){
//    $content='涛爷微博网址:http://www.wt530.cn @涛爷 [呵呵]';
    //给url地址加上<a>连接
    $preg='/(?:http:\/\/)?([\w.]+[\w\/]*\.[\w.]+[\w\/]*\??[\w=\$\+\%]*)/is';
    $content= preg_replace($preg,'<a href="http://\\1" target="_blank" >\\1<a>',$content);

    //给@用户加上<a>链接 \s :表示空 \S :表示非空格
    $preg='/@(\S+)\s/is';
    $content=preg_replace($preg,'<a href="'.__APP__.'/Home/User/\\1" >@\\1<a>',$content);

    //提取微博内容所有表情文件
    $preg='/\[(\S+?)\]/is';
    preg_match_all($preg,$content,$arr);
//    dump($arr);
    //载入表情包数组
    $phiz=include './App/Public/Data/phiz.php';
//    dump($phiz);
    if(!empty($arr[1])){
        foreach($arr[1] as $k => $v){
            $name=array_search($v,$phiz);
//               dump($name);
            if($name){
                $content=str_replace($arr[0][$k],'<img src="'.__ROOT__.'/App/Public/Images/phiz/'.$name.'.gif" title="'.$v.'"/>',$content);
            }
        }
    }
    // 返回内容并过虑关键字
//    return str_replace(C('FILTER'), '***', $content);
    return $content;
}

/**
 * @param $uid 推送消息属于的用户
 * @param $type 1 表示评论 2表示私信 3表示@我的
 * @param $flush true 表示已读  false 表示未读
 */
function set_msg($uid,$type,$flush=false){
    $name='';
    switch($type){
        case 1:
            $name='comment';
            break;
        case 2:
            $name='letter';
            break;
        case 3:
            $name='atme';
            break;
    }

    if($flush){
        //当信息已读时，初始化数据
        $data[$name]['total']=0;
        $data[$name]['status']=0;
        S('userMsg'.$uid,$data,0);
        return;
    }
    //内存数据存在时(表示用户还未读推送消息),相应数据+1,并重新写入到内存
    if(S('userMsg'.$uid)){
        $data=S('userMsg'.$uid);//读取数据
        $data[$name]['total']++;
        $data[$name]['status']=1;//推送状态 1表示推送中，0表示推送成功，（已读)
        S('userMsg'.$uid,$data,0);//0表示永远存储在内存中，至到用户读取
    }else{
        //内存数据不存在时，那么就需要重新建立数据，也就是数据初始化，并写入到内存
        $data=array(
            'comment' => array('total' =>0,'status' =>0),
            'letter' => array('total' =>0,'status' =>0),
            'atme' => array('total' =>0,'status' =>0),
        );
        //这里状态变化是因为当调用此函数就表示有消息需要推送给用户
        $data[$name]['total']++;
        $data[$name]['status']=1;
        //存入内存中
        S('userMsg'.$uid,$data,0);
    }

}