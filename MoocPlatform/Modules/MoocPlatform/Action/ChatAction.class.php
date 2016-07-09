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
            $type = 0;
            $stu = session('student');
            $id = $stu['StuID'];
        }else{
            $type = 1;
            $tea = session('teacher');
            $id = $tea['TeaID'];
        }

        //$student = D('ChatMsg') -> getStudent(array('type' => 0));
        $teacher = D('ChatMsg') -> getTeacher(array('type' => 1));
        $all = D('ChatMsg') -> getAll();
        //p($all);die;

        $this->type = $type;
        $this->id = $id;
        $this->all = $all;
        $this->teacher = $teacher;

        $com_show = M('com_show') -> where(array('UserID' => $id , 'type' => $type)) ->find();
        if( empty($com_show) ){
            $data = array(
                'UserID' => $id,
                'type' => $type,
                'showTime' => time(),
            );
            M('com_show') -> add($data);
        }
        else{
            $data = array(
                'ID' => $com_show['ID'],
                'showTime' => time(),
            );
            M('com_show')->save($data);
        }

        $this->display();
    }

    public function fresh(){

        if(session("?student")){
            $type = 0;
            $stu = session('student');
            $id = $stu['StuID'];
        }else{
            $type = 1;
            $tea = session('teacher');
            $id = $tea['TeaID'];
        }


        $time =  M('com_show') -> where(array('id' => $id , 'type' => $type )) ->find() ;
        //$msg = M('communication') -> where( array( 'Times'=>array('gt', $time['showTime']) ,'UserID' => array('neq',$id) )) -> select();

//        if($type == 0){
//            $msg = D('ChatMsg') -> getStudent(array('Times'=> array('gt',$time['showTime']),'UserID' => array('neq',$id)));
//        }else{
//            $msg = D('ChatMsg') -> getTeacher(array('Times'=> array('gt',$time['showTime']),'UserID' => array('neq',$id)));
//        }


      $msg = D('ChatMsg') -> getAll(array('Times'=> array('gt',$time['showTime']),'UserID' => array('neq',$id)));

        if( !empty($msg) ){
            $data = array(
                'showTime' => time(),
            );
            M('com_show') -> where(array('id' => $id , 'type' => $type )) -> save($data);
        }

        //$msg['id'] = $id;
        $this->ajaxreturn($msg);

    }

    public function publish(){
        $type = I('type');
        $id = I('id');
        $msg = I('msg');

        $com = array(
            'CourseID' => 1,
            'UserID' => $id,
            'Times' => time(),
            'Content' => $msg,
            'type' => $type,
        );
        $id = M('communication') -> add($com);


        if($type == 0){
            $data = D('ChatMsg') -> getStudent(array('ID' => $id));
        }else{
            $data = D('ChatMsg') -> getTeacher(array('ID' => $id));
        }

        //$data = M('communication') -> where(array('ID' => $id)) ->select();
        //$data['time'] = date("Y-m-d H:i:s", $data[0]['Times']);

        $this->ajaxreturn($data);
    }

}
?>