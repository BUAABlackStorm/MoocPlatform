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

        $courses = array();
        /**
         * 单人加入的课程ID
         */
        $couStu = M('coursestudent')->where(array("StudentID" => $stuID))->select();

        // 查询所有课程名字
        $courses = array();
        $index = 0;
        foreach ($couStu as $value) {
            $courses[$index] = M('Course')->where('CourseID = %d', $value['CourseID'])->find();
            $index++;
        }

        /**
         * 团队加入的课程ID
         */
        /*$groupStu = M('groupstu')->where("StudentID = %d AND JoinStatus = 1", $stuID)->select();*/
        //foreach ($groupStu as $value) {
            //$tmpCourseIDs = M('groupcourse')->where('GroupID = %d', $value['GroupID'])->select();

            //foreach ($tmpCourseIDs as $v) {
                //$courses[$index] = M('Course')->where('CourseID = %d', $v['CourseID'])->find();
                //$courses[$index]['CourseGroupID'] = $v['GroupID'];
                //$index++;
            //}
        /*}*/
        //dump($courses);
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
            //session('CourseGroupID', I('CourseGroupID'));   // 如果是团队课程则记录GroupID
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
            $this->isOutdate = 0;
        } else {
            $this->isOutdate = 1;
        }

        // 判断是否可以提交附件
        $this->canSubmit = 0;
        if ($homework['isGroupHw'] == 0) {  // 单人作业可以提交
            $this->canSubmit = 1;
        } else {                            // 多人作业判断是否是组长
            // 查询加入该课程的所有GroupID 及 PrinID
            $allGroupIDs = M('groupcourse')->where('CourseID = %d', session('selectedCourseID'))->select(); 
            foreach ($allGroupIDs as $value) {
                $tmpPrinID = M('learninggroup')->where('GroupID = %d', $value['GroupID'])->find()['PrincipalID'];
                if ($tmpPrinID == $stuID) { // 是组长
                    $this->canSubmit = 1;
                    break;
                }
            }
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
            $hasJoinGroups[$key]['NowPerson'] = M('groupstu')->where('GroupID = %d AND JoinStatus = 1', $value['GroupID'])->count();
            
            // 查询改团队 已加入课程
            $hasJoinCourseIDs = M('groupcourse')->where('GroupID = %d AND ApplyStatus=1', $value['GroupID'])->select();
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
            $canJoinGroups[$index]['NowPerson'] = M('groupstu')->where('GroupID = %d AND JoinStatus = 1', $value['GroupID'])->count();
            
            // 查询改团队 已加入课程
            $canJoinCourseIDs = M('groupcourse')->where('GroupID = %d AND ApplyStatus=1', $value['GroupID'])->select();
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
     * 解散团队
     */
    public function groupdismiss() {
        M('learninggroup')->where('GroupID = %d', I('GroupID'))->delete();
        M('groupstu')->where('GroupID = %d', I('GroupID'))->delete();
        M('groupcourse')->where('GroupID = %d', I('GroupID'))->delete();

        $this->redirect('Student/group', '', 1, '解散成功，正在返回...');
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


    /**
     * 组建团队
     */
    public function groupbuild() {
        $this->display('Student/groupbuild');
    }
    
    public function groupbuildadd() {
        $group = array(
            'GroupName' => I('groupName'),
            'PrincipalID' => session('student')['StuID'],
            'MaxPerson' => I('groupMaxPer'),
            'BuildStatus' => 0,     // 正在组建团队
        );
        
        // TODO: 判断是否符合条件
        $groupID = M('Learninggroup')->add($group);

        $groupstu = array(
            'GroupID' => $groupID,
            'StudentID' => session('student')['StuID'],
            'JoinStatus' => 1,
        );
        M('groupstu')->add($groupstu);

        $this->redirect('Student/groupmanage', '', 1, '申请成功，正在返回...');
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
        $GroupID = I('selectGroup');

        //查找该组的组长ID
        $principalID = M('learninggroup') -> where(array('GroupID' => $GroupID)) -> getField('PrincipalID');

        //查找除组长之外的团队成员
        $groupMemberIDs = M('groupstu')->where(array('GroupID' => $GroupID , 'StudentID' => array('neq',$principalID), 'JoinStatus' => 1))->select();

        foreach ($groupMemberIDs as $key => $value) {
            $groupMembers[$key] = M('Student')->where('StuID = %d', $value['StudentID'])->field(array('StuID','StuName'))->find();
        }

        $this->ajaxreturn($groupMembers);
    }

    public function deleteMembers(){
        $StuID = I('StuID');
        $GroupID = I('GroupID');

        $delete = M('groupstu')->where(array('GroupID' => $GroupID , 'StudentID' => $StuID)) -> delete();
        $data = array();
        if($delete){
            $data['status'] = 'success';
            $this->ajaxreturn($data);
        }else{
            $data['status'] = 'error';
            $this->ajaxreturn($data);
        }
    }

    public function principal(){
        $StuID = I('StuID');
        $GroupID = I('GroupID');

        $data = array();

        //找到之前的组长ID
        $principalID = M('learninggroup') -> where(array('GroupID' => $GroupID)) -> getField('PrincipalID');
        $data['PrincipalID'] = $principalID;

        //更新learninggroup中的PrincipalID
        $saveMember = array(
            'PrincipalID' => $StuID,
        );
        $save = M('learninggroup') -> where(array('GroupID' => $GroupID)) ->save($saveMember);

        if($save){
            $data['status'] = 'success';
        } else {
            $data['status'] = 'error';
        }
        $this->ajaxreturn($data);
    }

    public function hasBuilded() {
        // 判断团队是否组建完毕
        $hasBuilded = M('learninggroup')->where('GroupID = %d', I('selectGroupID'))->find()['BuildStatus'];
        $this->ajaxreturn($hasBuilded);
    }

    /**
     * 
     */
    public function handleapply() {
        $selectManageGroupID = I('selectGroupID');

        // 获取所有 申请团队ID
        $groupApplyMemberIDs = M('groupstu')->where('GroupID = %d AND JoinStatus = 0', $selectManageGroupID)->select();
        foreach ($groupApplyMemberIDs as $key => $value) {
            $groupApplyMembers[$key] = M('Student')->where('StuID = %d', $value['StudentID'])->find();
        }
        
        $this->ajaxreturn($groupApplyMembers);
        //dump($groupApplyMembers);
        //$this->display('Student/groupmanage');
    }

    /**
     * 同意加入 or 拒绝加入团队
     */
    public function agreerefuse() {       
        $data['JoinStatus'] = I('flag');
        $result = M('groupstu')->where('GroupID = %d AND StudentID = %d', I('selectGroupID'), I('StuID'))->save($data);
        if (!$result) {
            $data['status'] = 'error';
        }
        $this->ajaxreturn($data);
    }

    /**
     * 结束组建团队
     */
    public function endbuild() {
        $data['BuildStatus'] = 1;
        $result = M('learninggroup')->where('GroupID = %d', I('selectGroupID'))->save($data); 
        if (!$result) {
            $data['status'] = 'error';
        }
        $this->ajaxreturn($data);
    }

    /**
     * 申请课程
     * TODO: 区分正在申请否？
     */
    public function canapplycourse() {
        // 找出所有已申请的CourseID
        $hasJoinCourseIDs = M('groupcourse')->where('GroupID = %d', I('selectGroupID'))->getField('CourseID', true);
        // 找出所有开设的课程
        $allOpenCourses = M('Course')->where('isOpen = 1')->select();
        
        $canJoinCourses = array();
        $index = 0;
        foreach ($allOpenCourses as $key => $value) {
            if (in_array($value['CourseID'], $hasJoinCourseIDs)) {
                continue;   // 团队 已加入该课程
            }
            
            $canJoinCourses[$index] = $value;
            ++$index;
        }
        $this->ajaxreturn($canJoinCourses);
    }

    public function applycourse() {
        $data['GroupID'] = I('selectGroupID');
        $data['CourseID'] = I('CourseID');
        $data['ApplyStatus'] = 0;

        //判断该团队是否有资格加入课程（判断团队中是否有成员已经在其他团队加入该课程）
        $flag = true;
        $condition = array(
            'GroupID' => $data['GroupID'],
            'JoinStatus' => 1,
        );

        $members = M('groupstu') -> where($condition) -> field('StudentID') -> select();
        
        foreach($members as $key => $v){
            //查询改组中某名同学加入的所有小组
            $groups = M('groupstu')->where( array('StudentID' => $v['StudentID'] ,'JoinStatus' => 1) ) ->field('GroupID')->select();

            //判断改组是否加入该门课程
            foreach($groups as $key1 => $v1 ){
                //查询改组加入的所有课程
                $courseID = M('groupcourse') ->where(array('GroupID' => $v1['GroupID'],'ApplyStatus' => array('neq' , 2) ))->filed('CourseID')->select();
                foreach($courseID as $key2 => $v2){
                    //判断加入的课程是否等于申请团队申请的课程
                    if($data['CourseID'] == $v2['CourseID']){
                        $flag = false;
                    }
                }
            }
        }
        
        if ($flag) {
            $result = M('groupcourse')->add($data);
            if ($result) {
                $data['status'] = 'success';
            } else {
                $data['status'] = 'fail';
            }
        } else {
            $data['status'] = 'error';
        }
        $this->ajaxreturn($data);
    }
}
