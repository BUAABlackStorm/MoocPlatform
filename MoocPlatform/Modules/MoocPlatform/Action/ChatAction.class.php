<?php
/**
 * Created by PhpStorm.
 * User: Lenovo
 * Date: 2016/7/6
 * Time: 9:24
 */

Class ChatAction extends Action{

    public function chat(){

        if(session("?student")){
            $this->type = 1;
            $this->id = session('student')['StuID'];
        }else{
            $this->type = 2;
            $this->id = session('teacher')['TeaID'];
        }

        $this->display();
    }

    public function fresh(){

    }

    public function publish(){
        $type = I('type');
        $id = I('id');
        $msg = I('msg');

        $com = array(
            'CourseID' => 1,
            'UserID' => $id,
            'Time' => time(),
            'Content' => $msg,
            'isShow' => 0,
        );

        $data = M('communication') -> add($com);
        $this->ajaxreturn($data);
    }

}
?>