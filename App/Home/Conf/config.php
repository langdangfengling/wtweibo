<?php
return array(
    //'配置项'=>'配置值'
//    'DEFAULT_THEME'         =>  'default',//默认模板主题
    //修改__PUBLIC__路径
    'TMPL_PARSE_STRING' =>array(
        '__PUBLIC__' =>__ROOT__.'/App/Public',
        '__UPLOADS__' =>__ROOT__.'/Uploads',
    ),
    //保存自动登录的cookie时间 一个星期
    'AUTO_LOGIN_TIME' => time()+7*24*60*60,
    //用户异位或加密的KEY(cookie加密)
    'ENCRYPTION_KEY' => 'wtlovetcy',
    //图片上传地址
    'UPLOAD_PATH' =>'./Uploads/',
    //URL路由配置
    'URL_ROUTER_ON' => true,//开启路由功能
    'URL_ROUTE_RULES' => array(//定义路由规则
        ':uid\d' => 'Home/User/index',//表示只会匹配数字参数，如果你需要更加多的变量类型检测
        'follow/:uid\d' =>array('Home/User/followList','type=1'),//1为关注，0为粉丝
        'fans/:uid\d' => array('Home/User/followList','type=0'),
//        'photo/:uid\d' => 'Home/User/photo', //相册
    ),
    //自定义标签配置
    'TAGLIB_PRE_LOAD' => 'MhTags',//加载自定义标签库
    'TAGLIB_BUILD_IN' => 'Cx,MhTags', //加入系统标签库

    //缓存设置
    'DATA_CACHE_SUBDIR' => true, //开启以哈希形式生成缓存目录
    'DATA_PATH_LEVEL' => 2, //目录层次
//    'DATA_CACHE_TYPE' => 'Memcache',
//    'MEMCACHE_HOST' => '127.0.0.1',
//    'MEMCACHE_PORT' => '11211',
    //配置扩展
    'LOAD_EXT_CONFIG' => 'system',

    //多层嵌套
    'TAG_NESTED_LEVEL' =>10,
);