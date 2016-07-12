<?php
/**
 * Created by PhpStorm.
 * User: Wayne
 * Date: 2016/7/8
 * Time: 14:39
 */


Class ChatMsgModel extends RelationModel{
    Protected $tableName = 'communication'; //主表

    Protected $_link =  array(
        'student' => array(
            'mapping_type' => BELONGS_TO,
            'foreign_key' => 'UserID',
            'mapping_fields' => 'StuName',
        ),

        'teacher' => array(
            'mapping_type' => BELONGS_TO,
            'foreign_key' => 'UserID',
            'mapping_fields' => 'TeaName',
        ),
    );

    public function getStudent($condition = array()){
        return $this->where($condition)->relation('student')->select();
    }

    public function getTeacher($condition = array()){
        return $this->where($condition)->relation('teacher')->select();

    }

    public function getAll($condition = array()){
        return $this->where($condition)->relation(true)->select();
    }


}
?>