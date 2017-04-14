<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/7 0007
 * Time: 21:20
 */

namespace Model;


use Think\Model\RelationModel;

class AlbumModel extends RelationModel
{
   public $tableName='album';
    protected $_link=array(
        'photo' => array(
            'mapping_type' => self::HAS_MANY,
            'foreign_key' => 'aid'
        ),
    );
    public function del($id){
        if($id) {
            $this->relation(true)->delete($id);
        }
    }
}