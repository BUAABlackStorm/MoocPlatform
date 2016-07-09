<?php
/**
 * Created by chenkl
 * Date: 2016/07/04
 */

class StudentAction extends Action {

    public function index() {
        $this->display();
    }

    /**
     * 学生个人信息
     * 参数: $stuID
     */
    public function studentinfo() {
        $stu = session('student');
        $stuID = $stu['StuID'];

        $Student = M('Student');
        $stu = $Student->where('StuID = %d', $stuID)->find();

        $this->stu = $stu; 

        $this->display('Student/studentinfo');
    }
    
    /**
     * 学生课程信息
     * 参数: $stuID
     */
    public function courseinfo() {
        $stu = session('student');
        $stuID = $stu['StuID'];

        $couStu = M('coursestudent')->where(array("StudentID" => $stuID))->select();
        //$this->couStu = $couStu;

        $courses = array();
        foreach ($couStu as $key=>$value) {
            $courses[$key] = M('Course')->where('CourseID = %d', $value['CourseID'])->find();
        }
        $this->courses = $courses;
        
        $this->display('Student/courseinfo');        
    }
    
    /**
     * 个人作业信息
     * 参数: $stuID
     */
    /*public function indiwork() {*/
        //$stuID = 13211032;
        //$couStu = M('coursestudent')->where(array("StudentID" => $stuID))->select();
        
        //$courses = array();
        //foreach ($couStu as $key=>$value) {
            //$courses[$key] = M('Course')->where('CourseID = %d', $value['CourseID'])->find();
            //$homeworks[$key] = M('Homework')->where('CourseID = %d', $value['CourseID'])->select();            
        //}            
        //$this->courses = $courses;
        //$this->homeworks = $homeworks;
        //dump($courses);
        //$this->display('Student/indiwork');
    //}

    /**
     * 课程资源界面
     */
    public function resource() {

        if (I('CourseID') == '')
        {
            $CourseID = session('selectedCourseID');
        } else {
            $CourseID = I('CourseID');
            session('selectedCourseID', $CourseID);
        }

        $kejianCount = M('Resource')->where('ResKindID=1 AND CourseID=%d', $CourseID)->count();
        $docCount = M('Resource')->where('ResKindID=2 AND CourseID=%d', $CourseID)->count();
        $videoCount = M('Resource')->where('ResKindID=3 AND CourseID=%d', $CourseID)->count();
        
        $this->kejianCount = $kejianCount;
        $this->docCount = $docCount;
        $this->videoCount = $videoCount;

        $this->display('Student/resource');
    }
    
    /**
     * 三种具体的资源
     */
    public function threeRes() {
        //dump(session('selectedCourseID'));
        //dump(session());

        //dump(I('resKindID')); 
        $ress = M('Resource')->where('ResKindID = %d AND CourseID=%d', I('resKindID'), session('selectedCourseID'))->select();
        $this->ress = $ress;
       
        $this->display('Student/resThree');
    }
    
    /**
     * 下载资源
     */
    public function download() {
        //dump(I('resID'));

        $res = M('Resource')->where('ResID = %d', I('resID'))->find();

        import('ORG.Net.Http');
        Http::download($res['ResPath'].$res['ResActualName'],urlencode($res['ResOriginName']) );
    }

    /**
     * 作业
     * 参数: $couID 已选课程ID
     */
    public function homework() {
        //$couID = 1;
        //dump($couID);
        $couID = session('selectedCourseID');

        $homeworks = M('Homework')->where(array("CourseID" => $couID))->select();
        $this->homeworks = $homeworks;

        $this->display('Student/homework');
    }

    /**
     * 提交作业
     * 参数: $stuID 学号
     */
    public function submithomework() {
        //p(I('HwID'));
        $stu = session('student');
        $stuID = $stu['StuID'];
        //p($stuID);

        session('selectedHwID', I('HwID'));
        //p(session('selectedHwID'));
        
        $homework = M('Homework')->where(array("HwID" => I('HwID')))->find();
        $this->homework = $homework;
        
        // 判断作业是否结束
        if ( (strtotime("now") >= strtotime($homework['StartDate']))
            and (strtotime("now") <= strtotime($homework['EndDate'])) ) {
            $this->isOutdate = 1;
        } else {
            $this->isOutdate = 0;
        }
            
        
        $condition['StuID'] = $stuID;
        $condition['HwID'] = I('HwID');
        $hwstu = M('Hwstu')->where($condition)->find();
        $this->hwstu = $hwstu;
        //p($hwstu);

        session('selectedHwstuID', $hwstu['ID']);

        //p(session('selectedHwstuID'));
        
        // 获得已上传附件
        $files = M('Hwres')->where(array("HwStuID" => $hwstu['ID']))->select();
        $attachment = array();
        foreach ($files as $key=>$value) {
            //dump($value['ResID']);
            $tmp = M('Resource')->where(array("ResID" => $value['ResID']))->find();
            $attachment[$key] = $tmp;
        }
        $this->attachment = $attachment;
        //p($attachment);
        
        $this->display('Student/submithomework');
    }

    /*
     * TODO: delete
     * resource     hwres
     */
    public function delete() {
        //dump(I('ResID'));
        $delFilepath = M('Resource')->where(array("ResID" => I('ResID')))->find();
        unlink($delFilepath['ResPath'].$delFilepath['ResActualName']);
        
        M('Resource')->where(array("ResID" => I('ResID')))->delete();
        M('Hwres')->where(array("ResID" => I('ResID')))->delete();
        
        $this->redirect('Student/submithomework', array('HwID' => session('selectedHwID')), 1, '页面跳转中...');
    }

    public function upload() {
        //dump(I('content'));

        $stu = session('student');
        $stuID = $stu['StuID'];

        import('ORG.Net.UploadFile');
        $config = array(
            'maxSize'    =>    3145728,
            'savePath'   =>    './MoocPlatform/Modules/MoocPlatform/Uploads/Student/',
            'saveRule'   =>    'uniqid',
            'allowExts'  =>    array('jpg', 'png', 'jpeg','doc','docx','xls','xlsx','ppt','pptx','txt'),
            'autoSub'    =>    true,
            'subType'	 =>	   'date',
            'dateFormat'    =>    'Y-m-d',
        );
        $upload = new UploadFile($config);
        $upload->upload();
        $info= $upload->getUploadFileInfo();
        
        if(!$info && !I('content'))
        {
            //$this->error($upload->getError());
            $this->redirect('Student/submithomework', array('HwID' => session('selectedHwID')), 2, '请填写作业内容...');
        }
        else
        {
            /**
             * 获取HwStu ID
             * 参数:
             *  StuID 学生ID: session获取
             *  HwID  作业ID: session获取
             */
            $condition['StuID'] = $stuID;
            $condition['HwID'] = session('selectedHwID');
            $hwstu = M('Hwstu')->where($condition)->find();
            $this->hwstu = $hwstu;

            $hwstuID = $hwstu['ID'];
            // 第一次提交, 创建HwStu记录, 后面只要修改即可
            if ($hwstu['ID'] == '')
            {
                $hs = array(
                    'HwID' => session('selectedHwID'),
                    'StuID' => $stuID,
                    'Content' => I('content'),
                );
                $hwstuID = M('Hwstu')->add($hs);
            } else {    // 更新HwStu文本作业内容
                if (I('content') != '') {
                    $data['Content'] = I('content');
                    M('Hwstu')->where('ID = %d', $hwstu['ID'])->save($data);
                }
            }

            //p($hwstuID);

            // 添加附件
            foreach($info as $file)
            {
                $res = array(
                    'OwnerID'=>$stuID,
                    'CourseID'=>session('selectedCourseID'),
                    'ResOriginName'=>$file['name'],
                    'ResActualName'=>$file['savename'],
                    'ResPath'=>$file['savepath'],
//                    'ResKindID'=>$res_kind_id,
                    'FileSize'=>$file['size']
                );
                $result = M('resource')->add($res);

                $hr = array(
                    'HwStuID' => $hwstuID,
                    'ResID' => $result,
                );
                M('Hwres')->add($hr);
            }

            $this->redirect('Student/submithomework', array('HwID' => session('selectedHwID')), 1, '页面跳转中...');
        }
    }

    public function group() {
        $stuID = session('student')['StuID'];

        // 查询加入的所有团队id, 并获得团队基本信息
        $hasJoinGroupStus = M('groupstu')->where('StudentID = %d AND JoinStatus = 1', $stuID)->select();
        $hasJoinGroups = array();
        foreach ($hasJoinGroupStus as $key => $value) {
            $hasJoinGroups[$key] = M('Learninggroup')->where('GroupID = %d', $value['GroupID'])->find();

            // 查询该团队 负责人
            $stuPrin = M('Student')->where('StuID = %d', $hasJoinGroups[$key]['PrincipalID'])->find();
            $hasJoinGroups[$key]['PrinName'] = $stuPrin['StuName'];
            
            // 查询该团队 已加入的人数
            $hasJoinGroups[$key]['NowPerson'] = M('groupstu')->where('GroupID = %d', $value['GroupID'])->count();
            
            // 查询改团队 已加入课程
            $hasJoinCourseIDs = M('groupcourse')->where('GroupID = %d', $value['GroupID'])->select();
            foreach ($hasJoinCourseIDs as $k => $v) {
                $hasJoinGroups[$key]['Courses'][$k] = M('Course')->where('CourseID = %d', $v['CourseID'])->find();
            }
        }
        //p($hasJoinGroups);
        $this->hasJoinGroups = $hasJoinGroups;
        
        // 查询可加入的团队id及信息
        // 已加入所有团队ID
        $hasJoinGroupIDs = M('groupstu')->where('StudentID = %d AND JoinStatus = 1', $stuID)->getField('GroupID', true);
        // 所有非自己创建的团队
        $notMeBuildGroups = M('Learninggroup')->where('PrincipalID != %d', $stuID)->select();
        $canJoinGroups = array();
        $index = 0;
        foreach ($notMeBuildGroups as $key => $value) {
            if (in_array($value['GroupID'], $hasJoinGroupIDs)) { // 已加入团队
                continue;
            }
            $canJoinGroups[$index] = $value;

            // 查询该团队 负责人
            $stuPrin = M('Student')->where('StuID = %d', $canJoinGroups[$index]['PrincipalID'])->find();
            $canJoinGroups[$index]['PrinName'] = $stuPrin['StuName'];
            
            // 查询该团队 已加入的人数
            $canJoinGroups[$index]['NowPerson'] = M('groupstu')->where('GroupID = %d', $value['GroupID'])->count() + 1;
            
            // 查询改团队 已加入课程
            $canJoinCourseIDs = M('groupcourse')->where('GroupID = %d', $value['GroupID'])->select();
            foreach ($canJoinCourseIDs as $k => $v) {
                $canJoinGroups[$index]['Courses'][$k] = M('Course')->where('CourseID = %d', $v['CourseID'])->find();
            }

            // 查询自己加入该团队的状态
            $canJoinGroups[$index]['JoinStatus'] = M('groupstu')->where('StudentID = %d AND GroupID = %d', $stuID, $value['GroupID'])->find()['JoinStatus'];
            
            ++$index;
        }
        $this->canJoinGroups = $canJoinGroups;        
        //dump($canJoinGroups);
        
        $this->display('Student/group');
    }

    /**
     * 退出团队
     */
    public function groupexit() {
        //dump(I('GroupID'));
        $groupstu = array(
            'GroupID' => I('GroupID'),
            'StudentID' => session('student')['StuID'],
        );
        
        M('groupstu')->where($groupstu)->delete();
        $this->redirect('Student/group', '', 1, '删除成功，正在返回...');
    }

    /**
     * 申请加入团队
     */
    public function applyjoin() {
        $groupstu = array(
            'GroupID' => I('GroupID'),
            'StudentID' => session('student')['StuID'],
            'JoinStatus' => 0,  // 申请加入
        );
        
        M('groupstu')->data($groupstu)->add();
        $this->redirect('Student/group', '', 1, '申请成功，正在返回...');
    }

    public function groupbuild() {

        $this->display('Student/groupbuild');
    }
    
    /**
     * 组建团队
     */
    public function groupbuildadd() {
        $group = array(
            'GroupName' => I('groupName'),
            'PrincipalID' => session('student')['StuID'],
            'MaxPerson' => I('groupMaxPer'),
        );

        // TODO: 判断是否符合条件
        M('Learninggroup')->add($group);
    }

    /**
     * 管理团队
     */
    public function groupmanage() {
        $manageGroups = M('Learninggroup')->where('PrincipalID = %d', session('student')['StuID'])->select();
        //dump($manageGroups);
        $this->manageGroups = $manageGroups;

        $this->display('Student/groupmanage');
    }    
    
    public function selectgroups() {
        $selectGroup = I('selectGroup');
        $groupMemberIDs = M('groupstu')->where('GroupID = %d', $selectGroup)->select();

        foreach ($groupMemberIDs as $key => $value) {
            $groupMembers[$key] = M('Student')->where('StuID = %d', $value['StudentID'])->find()['StuName'];
        }

        $data['groupMembers'] = $groupMembers;
        $this->ajaxreturn($data);
    }
}
