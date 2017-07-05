<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/6 0006
 * Time: 21:34
 */

namespace Admin\Controller;

use Think\Controller;
class SystemController extends AdminBaseController
{
    /**
     * 网站设置
     */
    public function index(){
        $this->conf = include "./App/Home/Conf/system.php";
//        dump($this->config);die;

        $this->display();
    }

    //修改网站设置
    public function editSystem(){
        $path='./App/Home/Conf/system.php';
        $config= include $path;
        $config['WEBNAME']=I('post.webname');
        $config['COPY']=I('post.copy');
        $config['REGIS_ON']=I('post.regisOn');
        $data="<?php\r\nreturn ".var_export($config,true).";\r?>";
        if(file_put_contents($path,$data)){
            $this->success('修改成功',U('index'));
        }else{
            $this->error('修改失败，请修改'.$path.'文件的权限');
        }
    }
    //关键字过滤视图
    public function filter(){
        //获取配置文件中已设置的关键字(数组)
        $config= include  './App/Home/Conf/system.php';
//        dump($filter);die;
        //将数组用|字符分割成字符串输出到模板中
        $filter=implode('|',$config['FILTER']);
        $this->filter=$filter;
        $this->display();
    }

    //关键字过滤修改
    public function runEditFilter(){
        $path='./App/Home/Conf/system.php';
        $config= include $path;
        $filter=I('post.filter');
        //将字符串变成数组
        $config['FILTER']=explode("|",$filter);
        //将得到到字符串变成以配置文件中的字符串存储到配置文件中
        echo $filter;
//            dump($config);
//            dump(var_export($config,true));die;
        $data="<?php\n\r return ". var_export($config,true).";?>";
        if(file_put_contents($path,$data)){
            $this->success('修改成功',U('filter'));
        }else{
            $this->error('修改失败,请修改'.$path.'写入权限!');
        }
    }
}