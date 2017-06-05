<?php
// +----------------------------------------------------------------------
// | 菜单管理控制器
// +----------------------------------------------------------------------
// | date:2017-05-17
// +----------------------------------------------------------------------
// | Author: lzb
// +-------------------------------------------------
namespace Admin\Controller;
/**
 * 后台菜单管理
 */
class NavController extends AdminBaseController{
	/**
	 * 菜单列表
	 */
	public function index(){
		$data=D('AdminNav')->getTreeData('tree','order_number DESC,id'); //获取菜单列表
		$assign=array(
			'data'=>$data
			);
		$this->assign($assign);
		$this->display();
	}

	/**
	 * 添加菜单
	 */
	public function add(){
		if(IS_POST){
            $pid = I('post.pid');
            $name = trim(I('post.name'));
            $mca = trim(I('post.mca'));
			if(empty($name) || empty($mca)) $this->error('抱歉！参数不全');
            //查看mca是否已存在
            $AdminNav_db = D('AdminNav');
            $AuthRule_db = D('AuthRule');
            $bool = $AdminNav_db->getDataOne(array('mca'=>$mca),'id');
            if(!empty($bool))
            {
                $this->error('链接已存在，请核对');
            }
            //插入数据
            $data = array(
                'pid' => $pid,
                'name' => $name,
                'mca' => $mca
            );
			$result = $AdminNav_db->addData($data);
//            var_dump($result);die;
			if ($result) {
                //添加相应的权限
                $rule_data = array();
                if($data['pid'] == 0)
                {
                    $rule_data['pid'] = 0;
                }
                else
                {
                    //查询上级
                    $pid_name = $AdminNav_db->getDataOne(array('id'=>$pid,'pid'=>0),'mca');
                    $rule_info = $AuthRule_db->getDataOne(array('name'=>$pid_name['mca'],'pid'=>0),'id');
                    $rule_data['pid'] = $rule_info['id'];
                }
                $rule_data['name'] = $data['mca'];
                $rule_data['title'] = $data['name'];
                $rule_result = $AuthRule_db->addData($rule_data);
                if($rule_result)
                {
                    //更新auth_group表的rules权限
                    $this->edit_auth_group($rule_result);
                    $this->success('添加成功',U('Admin/Nav/index'));
                }
                else
                {
                    $this->success('添加菜单成功，权限失败',U('Admin/Nav/index'));
                }
			}else{
				$this->error('添加失败');
			}
		}else{
			$this->pid = I('id', 0);
			$this->type = $this->pid ?  '添加子菜单' : '添加菜单';
			$this->display();
		}		
	}

    /*
     * 修改超级管理员的auth_group权限
     * $auth_rule_id 新增的auth_rule表id
     * */
    public function edit_auth_group($auth_rule_id)
    {
        if(empty($auth_rule_id) || $auth_rule_id < 1)
        {
            return false;
        }
        $AuthGroup_db = D('AuthGroup');
        $group_info = $AuthGroup_db->field('id,rules')->where('id=1 and status=1')->find();
        if(empty($group_info))
        {
            return false;
        }
        if(empty($group_info['rules']))
        {
            $str = $auth_rule_id;
        }
        else
        {
            $str = $group_info['rules'].','.$auth_rule_id;
        }
        return $AuthGroup_db->editData(array('id'=>$group_info['id']),array('rules'=>$str));
    }

	/**
	 * 修改菜单
	 */
	public function edit(){
		$AdminNav = D('AdminNav');
		if(IS_POST){
			$data=I('post.');
			if(empty($data['name']) || empty($data['mca'])) $this->error('抱歉！参数不全');
            //查询信息是否存在
            $nav_info = $AdminNav->getDataOne(array('id'=>$data['id']),'mca');
            if(empty($nav_info))
            {
                $this->error('信息异常');
            }
			$map=array(
				'id'=>$data['id']
				);
			$result=$AdminNav->editData($map,$data);
			if ($result) {
                //相关权限也修改
                $rule_result = D('AuthRule')->editData(array('name'=>$nav_info['mca']),array('name'=>$data['mca'],'title'=>$data['name']));
                if($rule_result)
                {
                    $this->success('修改成功',U('Admin/Nav/index'));
                }
                else
                {
                    $this->success('修改成功,修改权限失败',U('Admin/Nav/index'));
                }
			}else{
				$this->error('修改失败');
			}
		}else{
			$id = I('id', 0);
			$data = $AdminNav->find($id);
			if(empty($data)) $this->error('找不到指定菜单', U('Nav/index'));
			$this->assign('data', $data);
			$this->display();
		}
	}

	/**
	 * 删除菜单
	 */
	public function delete(){
		$id=I('get.id');
		$map=array(
			'id'=>$id
			);
        $AdminNav = D('AdminNav');
        $AuthRule_db = D('AuthRule');
        //查询信息是否存在
        $nav_info = $AdminNav->getDataOne($map,'mca');
        if(empty($nav_info))
        {
            $this->success('删除成功',U('Admin/Nav/index'));
        }
		$result=$AdminNav->deleteData($map);
		if($result){
            //删除对应的权限
            $rule_info = $AuthRule_db->getDataOne(array('name'=>$nav_info['mca']),'id');
            if(!empty($rule_info))
            {
                $rule_result = $AuthRule_db->where(array('id'=>$rule_info['id']))->delete();
                if($rule_result)
                {
                    //删除所有的子权限
                    $AuthRule_db->where(array('pid'=>$rule_info['id']))->delete();
                    $this->success('删除成功',U('Admin/Nav/index'));
                }
                else
                {
                    $this->success('删除成功,权限删除失败',U('Admin/Nav/index'));
                }
            }
            else
            {
                $this->success('删除成功,权限删除失败',U('Admin/Nav/index'));
            }
		}else{
			$this->error('请先删除子菜单');
		}
	}
}
