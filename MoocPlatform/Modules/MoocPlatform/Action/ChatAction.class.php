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


        //p($teacher);

        $com_show = M('com_show')->where(array('UserID' => $id, 'type' => $type,'CourseID' => $courseID))->find();
        if (empty($com_show)) {
            $data = array(
                'UserID' => $id,
                'type' => $type,
                'showTime' => time(),
                'CourseID' => $courseID,
            );
            M('com_show')->add($data);
        } else {
            $data = array(
                'ID' => $com_show['ID'],
                'showTime' => time(),
            );
            M('com_show')->save($data);
        }

        if ($type == 0) {
            $this->display();
        } else {
            $this->display('Teacher/teaChat');
        }


    }

    public function fresh()
    {

        $type = I('type');
        $id = I('id');
        $courseID = I('courseID');

        $time = M('com_show')->where(array('UserID' => $id, 'type' => $type,'CourseID'=>$courseID))->find();

//        $condition1 = array(
//            'UserID' => array('neq', $id),
//            'type'   => array('neq',$type),
//            '_logic' => 'or',
//        );
//        $condition = array(
//            '_complex' => $condition1,
//            'Times' => array('gt', $time['showTime']),
//            'CourseID' => $courseID,
//        );

        $condition = array(
            'UserID' => array('neq', $id),
            'Times' => array('gt', $time['showTime']),
            'CourseID' => $courseID,
        );

        $data['status'] = 0;
        $msg = D('ChatMsg')->getAll($condition);

        if (!empty($msg)) {
            $data = array(
                'showTime' => time(),
            );
            $data['status'] = 1;
            M('com_show')->where(array('UserID' => $id, 'type' => $type,'CourseID'=>$courseID))->save($data);
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


        //$this->ajaxreturn($data);
    }

}
?>