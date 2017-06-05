<?php
// +----------------------------------------------------------------------
// | 管理员模型
// +----------------------------------------------------------------------
// | date:2017-05-17
// +----------------------------------------------------------------------
// | Author: lzb
// +-------------------------------------------------
namespace Common\Model;
use Common\Model\BaseModel;
/**
 * ModelName
 */
class AdminModel extends BaseModel{
    // 自动验证
    protected $_validate=array(
        array('admin_name','','帐号已经存在',0,'unique',2), // 验证字段必填
    );

    // 自动完成
    protected $_auto=array(
        //array('admin_password','md5',1,'function') , // 对password字段在新增的时候使md5函数处理
        //array('register_time','time',1,'function'), // 对date字段在新增的时候写入当前时间戳
    );

    /**
     * 添加用户
     */
    public function addData($data){
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            // 验证不通过返回错误
            return false;
        }else{
            // 验证通过
            $result=$this->add($data);
            return $result;
        }
    }

    /**
     * 修改用户
     */
    public function editData($map,$data){
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            // 验证不通过返回错误
            return false;
        }else{
            // 验证通过
            $result=$this
                ->where($map)
                ->save($data);
            return $result;
        }
    }

    /**
     * 删除数据
     * @param   array   $map    where语句数组形式
     * @return  boolean         操作是否成功
     */
    public function deleteData($map){
        die('禁止删除用户');
    }
}
