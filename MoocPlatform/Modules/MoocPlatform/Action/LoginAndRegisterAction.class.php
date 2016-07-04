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
        $this->display();
    }

    //进入注册界面
    public function RegisterView(){

        $question = M('question')->select();
        $this->question = $question;
        $this->display();
    }


    //登录验证
    public function Login(){

        $type = I('type');

        //若类型为1则是学生登录
        if($type == 1 ){
            $student = M('student')->where(array('StuID' => I('StuID')))->find();
            if($student['Password'] == I('Password')){
                session('StuID',I('StuID'));
                $url = '';//登录成功后的跳转地址
                $this->success("登录成功",U(GROUP_NAME.$url),1);
            }else{
                $this->error("登录失败，账号或者密码错误");
            }
        }
        //类型为2则是老师登录
        else if($type == 2){
            $teacher = M('teacher')->where(array('TeaID' => I('TeaID')))->find();
            if($teacher['Password'] == I('Password')){
                session('TeaID',I('TeaID'));
                $url='';//登录成功后的跳转地址
                $this->success("登录成功",U(GROUP_NAME.$url),1);
            }else{
                $this->error("登录失败，账号或者密码错误");
            }
        }
        //类型为3则是教务登录
        else if($type == 3){
            $senate = M('senate')->where(array('SenID' => I('SenID')))->find();
            if($senate['Password'] == I('Password')){
                session('SenID',I('SenID'));
                $url = '/Manage/Index';
                $this->success("登录成功",U($url),1);
            }else{
                $this->error("登录失败，账号或密码错误");
            }
        }
    }

    //注册
    public function Register(){

        $type = I('userType');
        $successUrl = '/LoginAndRegister/LoginView'; //注册成功后的跳转地址

        //学生注册
        if($type == 1){

            $student = array(
                'StuID' => I('userID'),
                'StuName' => I('userName'),
                'Password' => md5(I('password')),
                'RealName' => I('realName'),
                'Email' => I('userEmail'),
                'Sex' => I('sexRadioOptions'),
                'Department' => I('userDepartment'),
                'Class' => I('userClass'),
                'question' => I('QuestionID'),
                'answer' => I('Answer'),
                'grade' => I('Grade'),
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
                'TeaID' => I('TeaID'),
                'TeaName' => I('TeaName'),
                'Password' => md5(I('Password')),
                'Sex' => I('Sex'),
                'Email' => I('Email'),
                'Department' => I('Department'),
                'QuestionID' => I('QuestionID'),
                'Answer' => I('Answer'),
            );

            if( M('teacher') -> add($teacher) ){
                $this->success('注册成功',U(GROUP_NAME.$successUrl),2);
            }else{
                $this->error("注册失败，请重试");
            }
        }
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

                $student = array(
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

}


?>