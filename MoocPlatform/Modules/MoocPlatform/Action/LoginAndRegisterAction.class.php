<?php
/**
 * Created by PhpStorm.
 * User: Wayne
 * Date: 2016/7/4
 * Time: 10:28
 */

Class LoginAndRegisterAction extends Action{

    //进入登录界面
    public function LoginView(){
        $this->display('login');
    }

    //进入注册界面
    public function RegisterView(){

        $question = M('question')->select();
        $this->question = $question;

        $department = M('department')->select();
        $this->department = $department;

        $this->display('register');
    }


    //登录验证
    public function Login(){

        $type = I('type');

        //若类型为1则是学生登录
        if($type == 1 ){
            $student = M('student')->where(array('StuID' => I('StuID')))->find();
            if($student['Password'] == md5(I('Password')) ){
                session(null);
                session('StuID',$student['StuID']);
                session('student',$student);
                $url = '/Student/courseinfo';//登录成功后的跳转地址
                $this->success("登录成功",U(GROUP_NAME.$url),1);
            }else{
                $this->error("登录失败，账号或者密码错误");
            }
        }
        //类型为2则是老师登录
        else if($type == 2){
            $teacher = M('teacher')->where(array('TeaID' => I('TeaID')))->find();
            if($teacher['Password'] == md5(I('Password')) ){
                session(null);
                session('TeaID',$teacher['TeaID']);
                session('teacher',$teacher);
                $url='/Teacher'; //登录成功后的跳转地址
                $this->success("登录成功",U(GROUP_NAME.$url),1);
            }else{
                $this->error("登录失败，账号或者密码错误");
            }
        }
        //类型为3则是教务登录
        else if($type == 3){
            $senate = M('senate')->where(array('SenID' => I('SenID')))->find();
            if($senate['Password'] == md5(I('Password')) ){
                session(null);
                session('SenID',$senate['SenID']);
                session('senate',$senate);
                $url = '/Manage/AdminCourse/courseList';
                $this->success("登录成功",U($url),1);
            }else{
                $this->error("登录失败，账号或密码错误");
            }
        }
    }

    //注册
    public function Register(){

        $type = I('type');
        $successUrl = '/LoginAndRegister/LoginView'; //注册成功后的跳转地址

        //学生注册
        if($type == 1){

            $student = array(
                'StuID' => I('userId'),
                'StuName' => I('userName'),
                'Password' => md5(I('password1')),
                'RealName' => I('realName'),
                'Email' => I('useremail'),
                'Sex' => I('sex'),
                'Department' => I('userDepartment'),
                'Class' => I('userClass'),
                'QuestionID' => I('question'),
                'Answer' => I('Answer'),
                'Grade' => I('grade'),
            );


            if( M('student')->add($student) ){
                $this->success('注册成功',U(GROUP_NAME.$successUrl),2);
            }else{
                $this->error("注册失败，请重试");
            }

        }
        //老师注册
        else if($type == 2){

            $teacher = array(
                'TeaID' => I('teaid'),
                'TeaName' => I('teaname'),
                'Password' => md5(I('password')),
                'Sex' => I('sex'),
                'Email' => I('email'),
                'Department' => I('department'),
                'QuestionID' => I('questionid'),
                'Answer' => I('Answer'),
            );


            if( M('teacher') -> add($teacher) ){
                $this->success('注册成功',U(GROUP_NAME.$successUrl),2);
            }else{
                $this->error("注册失败，请重试");
            }
        }
    }


    public function changePasswordView(){
        $question = M('question')->select();
        $this->question = $question;

        $this->display('changePassword');
    }

    //修改密码
    public function changePassword(){
        $type = I('type');

        //学生找回密码
        if($type == 1){

            $student = M('student')->where(array('StuID' => I('StuID')))->find();
            if($student['QuestionID'] == I('QuestionID') && $student['Answer'] == I('Answer') ){

                $student = array(
                    'StuID' => I('StuID'),
                    'Password' => md5(I('Password')),
                );

                if( M('student')-> save($student) ){
                    $this->success("修改密码成功",U(GROUP_NAME.'/LoginAndRegister/LoginView'));
                }else{
                    $this->error("修改密码失败，请重试");
                }

            }else{
                $this->error("所选问题或者问题答案不对,请重试");
            }

        }
        //老师找回密码
        else if($type == 2){
            $teacher = M('teacher')->where(array('TeaID' => I('TeaID')))->find();
            if($teacher['QuestionID'] == I('QuestionID') && $teacher['Answer'] == I('Answer') ){

                $teacher = array(
                    'TeaID' => I('TeaID'),
                    'Password' => md5(I('Password')),
                );

                if( M('teacher')-> save($teacher) ){
                    $this->success("修改密码成功",U(GROUP_NAME.'/LoginAndRegister/LoginView'));
                }else{
                    $this->error("修改密码失败，请重试");
                }
            }else{
                $this->error("所选问题或者问题答案不对,请重试");
            }
        }
    }

    public function Logout(){
        session(null);
        $this->redirect('/LoginAndRegister/LoginView');
    }


}


?>