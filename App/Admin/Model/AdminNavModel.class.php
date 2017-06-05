<?php
// +----------------------------------------------------------------------
// | 菜单管理模型
// +----------------------------------------------------------------------
// | date:2017-05-17
// +----------------------------------------------------------------------
// | Author: lzb
// +-------------------------------------------------
namespace Common\Model;
use Org\Nx\Data;
use Think\Auth;

/**
 * 菜单操作model
 */
class AdminNavModel extends BaseModel{

    /*
     * 查询数据是否存在
     * @param	array	$map	where语句数组形式
     * @param   string  $filed  要查询的字段
     * @return	数组	 操作是否成功
     */
    public function getDataOne($map,$filed='*')
    {
        $data = $this->field($filed)->where($map)->find();
        return $data;
    }

	/**
	 * 删除数据
	 * @param	array	$map	where语句数组形式
	 * @return	boolean			操作是否成功
	 */
	public function deleteData($map){
		$count=$this
			->where(array('pid'=>$map['id']))
			->count();
		if($count!=0){
			return false;
		}
		$this->where(array($map))->delete();
		return true;
	}

	/**
	 * 获取全部菜单
	 * @param  string $type tree获取树形结构 level获取层级结构
	 * @param  string $order
	 * @return array       	结构数据
	 */
	public function getTreeData($type='tree',$order=''){
		// 判断是否需要排序
		if(empty($order)){
			$data=$this->select();
		}else{
			$data=$this->order('order_number is null,'.$order)->select();//这里不明order_number is null啥意思
//			var_dump($data);
		}
		// 获取树形或者结构数据
		if($type=='tree'){
			import("Common.Org.Data");
			$data=Data::tree($data,'name','id','pid');//这句有个var_dump语句
		}elseif($type == "level"){
			import("Common.Org.Data");
			$data=Data::channelLevel($data,0,'&nbsp;','id');
			// 显示有权限的菜单
			$auth=new Auth();
			foreach ($data as $k => $v) {
				if ($auth->check($v['mca'],$_SESSION['admin']['admin_id'])) {//节点认证
					foreach ($v['_data'] as $m => $n) {
						if(!$auth->check($n['mca'],$_SESSION['admin']['admin_id'])){
							unset($data[$k]['_data'][$m]);
						}
					}
				}else{
					// 删除无权限的菜单
					unset($data[$k]);
				}
			}
		}
		return $data;
	}
}
