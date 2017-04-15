<?php
/**
 * 用户关联模型
 */

namespace Model;

use Think\Model\RelationModel;
class UserModel extends  RelationModel
{
    public $tableName='user';
    protected $_link=array(
        'userinfo' =>array(
            'mapping_type'   =>self::HAS_ONE,
            'foreign_key' =>'uid',
        ),
    );
    //存在关联数据时自动插入数据
    public function insert($data=null){
        $data=is_null($data)?$_POST:$data;
        // $this->relation(true)->add($data);//这种方式只能传数组，不能传字符串，对象
        return $this->relation(true)->data($data)->add();
    }
}
?>