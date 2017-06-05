<?php
// +----------------------------------------------------------------------
// | 权限分配控制器
// +----------------------------------------------------------------------
// | date:2017-05-17
// +----------------------------------------------------------------------
// | Author: lzb
// +-------------------------------------------------
namespace Admin\Controller;
/**
 * 后台权限管理
 */
class RuleController extends AdminBaseController{

    //******************权限***********************
    /**
     * 权限列表
     */
    public function index(){
        $data=D('AuthRule')->getTreeData('level','id','title');
        $assign=array(
            'data'=>$data
            );
        $this->assign($assign);
        $this->display();
    }

    /**
     * 添加权限
     */
    public function add(){
        $AuthRule_db = D('AuthRule');
        if(IS_POST){
            $pid = I('post.pid');
            $title = trim(I('post.title'));
            $name = trim(I('post.name'));
            if(empty($title) || empty($name)) $this->error('抱歉，缺少参数');
            //检测auth_rule表的name是否已经存在
            $auth_info = $AuthRule_db->getDataOne(array('name'=>$name),'id');
            if(!empty($auth_info))
            {
                $this->error('权限已存在，请核对');
            }
            //插入数据
            $data = array(
                'pid' => $pid,
                'title' => $title,
                'name' => $name
            );
            $result=$AuthRule_db->addData($data);
            if ($result) {
                $this->success('添加成功',U('Admin/Rule/index'));
            }else{
                $this->error('添加失败');
            }
        }else{
            $this->pid = I('id', 0);
            $this->type = $this->pid ?  '添加子权限' : '添加权限';
            $this->display();
        }
    }

    /**
     * 修改权限
     */
    public function edit(){
        $AuthRule_db = D('AuthRule');
        if(IS_POST){
            $id = I('post.id');
            $title = trim(I('post.title'));
            $name = trim(I('post.name'));
            if(empty($title) || empty($name)) $this->error('抱歉，缺少参数');
            //检测auth_rule表的name是否已经存在
            $auth_info = $AuthRule_db->getDataOne(array('name'=>$name),'id');
            if(!empty($auth_info))
            {
                $this->error('权限已存在，请核对');
            }
            $map=array(
                'id'=>$id
                );
            $data = array(
                'title' => $title,
                'name' => $name
            );
            $result = $AuthRule_db->editData($map,$data);
            if ($result) {
                $this->success('修改成功',U('Admin/Rule/index'));
            }else{
                $this->error('修改失败');
            }
        }else{
            $id = I('id', 0);
            $data = $AuthRule_db->find($id);
            if(empty($data)) $this->error('找不到指定内容', U('Rule/index'));
            $this->assign('data', $data);
            $this->display();
        }
    }

    /**
     * 删除权限
     */
    public function delete(){
        $id=I('get.id');
        $map=array(
            'id'=>$id
            );
        $result=D('AuthRule')->deleteData($map);
        if($result){
            $this->success('删除成功',U('Admin/Rule/index'));
        }else{
            $this->error('请先删除子权限');
        }
    }
    //*******************用户组**********************
    /**
     * 用户组列表
     */
    public function group(){
        $data=D('AuthGroup')->select();
        $assign=array(
            'data'=>$data
            );
        $this->assign($assign);
        $this->display();
    }

    /**
     * 添加用户组
     */
    public function add_group(){
        $title = trim(I('post.title'));
        if(empty($title))
        {
            $this->error('用户组名不能为空');
        }
        $data = array(
            'title' => $title
        );
        $result=D('AuthGroup')->addData($data);
        if ($result){
            $this->success('添加成功',U('Admin/Rule/group'));
        }else{
            $this->error('添加失败');
        }
    }

    /**
     * 修改用户组
     */
    public function edit_group(){
        $title = trim(I('post.title'));
        if(empty($title))
        {
            $this->error('用户组名不能为空');
        }
        $map=array(
            'id'=> I('post.id')
            );
        $data = array(
            'title' => $title
        );
        $result=D('AuthGroup')->editData($map,$data);
        if ($result) {
            $this->success('修改成功',U('Admin/Rule/group'));
        }else{
            $this->error('修改失败');
        }
    }

    /**
     * 删除用户组
     */
    public function delete_group(){
        $id=I('get.id');
        $map=array(
            'id'=>$id
            );
        $result=D('AuthGroup')->deleteData($map);
        if ($result) {
            $this->success('删除成功',U('Admin/Rule/group'));
        }else{
            $this->error('删除失败');
        }
    }

    //*****************权限-用户组*****************
    /**
     * 分配权限
     */
    public function rule_group(){
        if(IS_POST){
            $data=I('post.');
            $map=array(
                'id'=>$data['id']
                );
            $save['rules']=implode(',', $data['rule_ids']);
            $result=D('AuthGroup')->editData($map,$save);
            if ($result) {
                $this->success('操作成功',U('Admin/Rule/group'));
            }else{
                $this->error('操作失败');
            }
        }else{
            $id=I('get.id');
            $this->assign('id',$id);
            // 获取用户组数据
            $group_data=M('Auth_group')->where(array('id'=>$id))->find();
            $group_data['rules']=explode(',', $group_data['rules']);
            // 获取规则数据
            $rule_data=D('AuthRule')->getTreeData('level','id','title');
            $assign=array(
                'group_data'=>$group_data,
                'rule_data'=>$rule_data
                );
            $this->assign($assign);
            $this->display();
        }

    }
    //******************用户-用户组*******************
    /**
     * 添加成员
     */
    public function check_user(){
        $username=I('get.username','');
        $group_id=I('get.group_id');
        $group_name=M('Auth_group')->getFieldById($group_id,'title');
        $uids=D('AuthGroupAccess')->getUidsByGroupId($group_id);
        // 判断用户名是否为空
        if(empty($username)){
            $user_data='';
        }else{
            $user_data=M('Users')->where(array('username'=>$username))->select();
        }
        $assign=array(
            'group_name'=>$group_name,
            'uids'=>$uids,
            'user_data'=>$user_data,
            );
        $this->assign($assign);
        $this->display();
    }

    /**
     * 添加用户到用户组
     */
    public function add_user_to_group(){
        $data=I('get.');
        $map=array(
            'uid'=>$data['uid'],
            'group_id'=>$data['group_id']
            );
        $count=M('AuthGroupAccess')->where($map)->count();
        if($count==0){
            D('AuthGroupAccess')->addData($data);
        }
        $this->success('操作成功',U('Admin/Rule/check_user',array('group_id'=>$data['group_id'],'username'=>$data['username'])));
    }

    /**
     * 将用户移除用户组
     */
    public function delete_user_from_group(){
        $map=I('get.');
        $result=D('AuthGroupAccess')->deleteData($map);
        if ($result) {
            $this->success('操作成功',U('Admin/Rule/admin_user_list'));
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 管理员列表
     */
    public function admin_user_list(){
        $data=D('AuthGroupAccess')->getAllData();
//        var_dump($data);
        $assign=array(
            'data'=>$data
            );
        $this->assign($assign);
        $this->display();
    }

    /**
     * 添加管理员
     */
    public function add_admin(){
        if(IS_POST){
            $admin_name = trim(I('post.admin_name'));
            $admin_password = trim(I('post.admin_password'));
            $phone = trim(I('post.phone'));
            $email = trim(I('post.email'));
            $status = I('post.status');
            $group_ids = I('post.group_ids');
            if(empty($admin_name) || !isNames($admin_name,6,20))
            {
                $this->error('请输入6-20位的账号');
            }
            if(empty($admin_password) || !isPWD($admin_password,6,20))
            {
                $this->error('请输入6-20位的密码');
            }
            if(empty($group_ids))
            {
                $this->error('请选择管理组');
            }
            //检查账号是否已存在
            $admin_info = D('Admin')->field('admin_id')->where(array('admin_name'=>$admin_name))->find();
            if(!empty($admin_info))
            {
                $this->error('账号已存在');
            }
            $data = array(
                'admin_name' => $admin_name,
                'admin_password' => md5($admin_password),
                'phone' => $phone,
                'email' => $email,
                'status' => $status,
                'register_time' => time()
            );
            $result=D('Admin')->addData($data);
            if($result){
                if (!empty($group_ids)) {
                    foreach ($group_ids as $k => $v) {
                        $group=array(
                            'uid'=>$result,
                            'group_id'=>$v
                            );
                        D('AuthGroupAccess')->addData($group);
                    }                   
                }
                // 操作成功
                $this->success('添加成功',U('Admin/Rule/admin_user_list'));
            }
            else{
                $this->error('添加失败',U('Admin/Rule/admin_user_list'));
            }
        }else{
            $data=D('AuthGroup')->select();
            $assign=array(
                'data'=>$data
                );
            $this->assign($assign);
            $this->display();
        }
    }

    /**
     * 修改管理员
     */
    public function edit_admin(){
        if(IS_POST){
            $admin_id = I('post.admin_id');
            $admin_name = trim(I('post.admin_name'));
            $admin_password = trim(I('post.admin_password'));
            $phone = trim(I('post.phone'));
            $email = trim(I('post.email'));
            $status = I('post.status');
            $group_ids = I('post.group_ids');
            if(empty($admin_id))
            {
                $this->error('管理员id异常');
            }
            if(empty($admin_name) || !isNames($admin_name,6,20))
            {
                $this->error('请输入6-20位的账号');
            }
            if(empty($group_ids))
            {
                $this->error('请选择管理组');
            }
            //检查账号是否已存在
            $admin_info = D('Admin')->field('admin_id')->where("admin_name='".$admin_name."' and admin_id !=".$admin_id)->find();
            if(!empty($admin_info))
            {
                $this->error('该账号已存在');//验证修改后的用户名是否被人占用
            }
            // 修改权限
            D('AuthGroupAccess')->deleteData(array('uid'=>$admin_id));
            foreach ($group_ids as $k => $v) {
                $group=array(
                    'uid'=>$admin_id    ,
                    'group_id'=>$v
                    );
                D('AuthGroupAccess')->addData($group);//因为没有主键，删除了再重新添加
            }
            $data = array(
                'admin_name' => $admin_name,
                'phone' => $phone,
                'email' => $email
            );
            $data=array_filter($data);//array_filter对数组进行过滤，没有提供回调函数，将删除 $data  中所有等值为 FALSE  的条目
            $data['status'] = $status;
            // 如果修改密码则md5
            if (!empty($admin_password)) {
                if(empty($admin_password) || !isPWD($admin_password,6,20))
                {
                    $this->error('请输入6-20位的密码');
                }
                $data['admin_password']=md5($admin_password);
            }
            // 组合where数组条件
            $map=array(
                'admin_id'=>$admin_id
            );
            $result=D('Admin')->editData($map,$data);
            // 操作成功
            $this->success('编辑成功',U('Admin/Rule/admin_user_list'));
        }else{
            //在没有post数据过来的话，就读取原先数据并显示
            $id=I('get.id',0,'intval');
            // 获取用户数据
            $user_data=M('Admin')->find($id);
            // 获取已加入用户组
            $group_data=M('AuthGroupAccess')
                ->where(array('uid'=>$id))
                ->getField('group_id',true);
            // 全部用户组
            $data=D('AuthGroup')->select();
            $assign=array(
                'data'=>$data,
                'user_data'=>$user_data,
                'group_data'=>$group_data
                );
            $this->assign($assign);
            $this->display();
        }
    }
}
