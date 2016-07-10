<?php

/**
 * Created by PhpStorm.
 * User: Lenovo
 * Date: 2016/7/6
 * Time: 9:24
 */
Class ChatAction extends VerifyLoginAction
{

    public function chat()
    {

        if (session("?student")) {
            $type = 0;
            $stu = session('student');
            $id = $stu['StuID'];
        } else {
            $type = 1;
            $tea = session('teacher');
            $id = $tea['TeaID'];
        }

        $courseID = I('courseID');

        //$student = D('ChatMsg') -> getStudent(array('type' => 0));
        $teacher = D('ChatMsg')->getTeacher(array('type' => 1, 'CourseID' => $courseID));
        $all = D('ChatMsg')->getAll(array('CourseID' => $courseID));
        //p($all);die;

        $this->type = $type;
        $this->id = $id;
        $this->all = $all;
        $this->teacher = $teacher;
        $this->course = $courseID;

        $com_show = M('com_show')->where(array('UserID' => $id, 'type' => $type))->find();
        if (empty($com_show)) {
            $data = array(
                'UserID' => $id,
                'type' => $type,
                'showTime' => time(),
            );
            M('com_show')->add($data);
        } else {
            $data = array(
                'ID' => $com_show['ID'],
                'showTime' => time(),
            );
            M('com_show')->save($data);
        }

        if($type == 0){
            $this->display();
        }
        else{
            $this->display('Teacher/teaChat');
        }


    }

    public function fresh()
    {


        $type = I('type');
        $id = I('id');
        $courseID = I('courseID');
        $time = M('com_show')->where(array('UserID' => $id, 'type' => $type))->find();

        $msg = D('ChatMsg')->getAll(array('Times' => array('gt', $time['showTime']), 'UserID' => array('neq', $id), 'CourseID' => $courseID));

        if (!empty($msg)) {
            $data = array(
                'showTime' => time(),
            );
            M('com_show')->where(array('UserID' => $id, 'type' => $type))->save($data);
        }

        $this->ajaxreturn($msg);

    }

    public function publish()
    {
        $type = I('type');
        $id = I('id');
        $msg = I('msg');
        $courseID = I('courseID');

        $com = array(
            'CourseID' => $courseID,
            'UserID' => $id,
            'Times' => time(),
            'Content' => $msg,
            'type' => $type,
        );
        $id = M('communication')->add($com);


//        if($type == 0){
//            $data = D('ChatMsg') -> getStudent(array('ID' => $id));
//        }else{
//            $data = D('ChatMsg') -> getTeacher(array('ID' => $id));
//        }

        //$this->ajaxreturn($data);
    }

}

?>