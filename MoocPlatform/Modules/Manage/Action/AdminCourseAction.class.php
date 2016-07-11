<?php
/**
 * Created by PhpStorm.
 * User: zf
 * Date: 2016/7/3
 * Time: 19:08
 */

class AdminCourseAction extends Action{

    public function courseList(){
        $db = M('course');
        $list = $db
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->getField('course.CourseID,course.CourseName,department.DepartmentName,course.isOpen');
        $this->assign('list',$list);
        //dump($list);
        $this->display('AdminCourse/courseList');
    }

    public function courseInfo(){
        $courseID = $_POST['CourseID'];
        $db = M('course');

        $courseinfo_1 = $db
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$courseID)
            ->getField('course.CourseID,course.CourseName,department.DepartmentName,course.isOpen');

        foreach ($courseinfo_1 as $item){
            $courseinfo['CourseID'] = $item['CourseID'];
            $courseinfo['CourseName'] = $item['CourseName'];
            $courseinfo['DepartmentName'] = $item['DepartmentName'];
            $courseinfo['isOpen'] = $item['isOpen'];
        }
        $this->assign('courseinfo',$courseinfo);

        $courseTeacher = $db
            ->join('courseteacher ON course.CourseID = courseteacher.CourseID')
            ->join('teacher ON teacher.TeaID = courseteacher.TeacherID')
            ->where('course.CourseID='.$courseID)
            ->getField('teacher.TeaName');
            //->select();
        $this->assign('courseTeacher',$courseTeacher);

        $courseStudent = $db
            ->join('coursestudent ON course.CourseID = coursestudent.CourseID')
            ->join('student ON student.StuID = coursestudent.StudentID')
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$courseID)
            ->getField('student.StuID,student.StuName,student.Email,department.DepartmentName,student.Grade');
            //->select();
        $this->assign('courseStudent',$courseStudent);

        $courseSchedule = $db
            ->join('courseschedule ON course.CourseID = courseschedule.CourseID')
            ->where('course.CourseID='.$courseID)
            ->select();
        $this->assign('courseSchedule',$courseSchedule);

        //dump($courseStudent);
        //$this->display('AdminCourse/courseList_1');
        $studentlist = null;
        $this->assign('studentlist',$studentlist);
        $this->display();
    }

    public function deleteStudent(){
        $courseID = $_POST['CourseID'];
        $studentID = $_POST['StuID'];

        if($courseID == null)
            $this->error('课程号不能为空','courseList');
        
        if($studentID == null)
            $this->error('学生学号不能为空','courseList');

        $db_1 = M('coursestudent');

        $result = $db_1->where("coursestudent.CourseID=".$courseID." AND coursestudent.StudentID =". $studentID)
            ->select();

        if($result == null)
            $this->error('该选项不存在','AdminCourse/courseList');

        $db_1 ->where("coursestudent.CourseID=".$courseID." AND coursestudent.StudentID =". $studentID)
              ->delete();


        $db = M('course');
        $courseinfo_1 = $db
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$courseID)
            ->getField('course.CourseID,course.CourseName,department.DepartmentName,course.isOpen');

        foreach ($courseinfo_1 as $item){
            $courseinfo['CourseID'] = $item['CourseID'];
            $courseinfo['CourseName'] = $item['CourseName'];
            $courseinfo['DepartmentName'] = $item['DepartmentName'];
            $courseinfo['isOpen'] = $item['isOpen'];
        }
        $this->assign('courseinfo',$courseinfo);

        $courseStudent = $db
            ->join('coursestudent ON course.CourseID = coursestudent.CourseID')
            ->join('student ON student.StuID = coursestudent.StudentID')
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$courseID)
            ->getField('student.StuID,student.StuName,student.Email,department.DepartmentName,student.Grade');
        //->select();
        $this->assign('courseStudent',$courseStudent);

        $courseSchedule = $db
            ->join('courseschedule ON course.CourseID = courseschedule.CourseID')
            ->where('course.CourseID='.$courseID)
            ->select();
        $this->assign('courseSchedule',$courseSchedule);

        $studentlist = null;
        $this->assign('studentlist',$studentlist);
        $this->display('AdminCourse/studentList');
    }
    
    public function alterCourseInfo()
    {
        $courseID = $_POST['CourseID'];
        $ScheduleID = $_POST['ScheduleID'];
        $CourseStartWeek = $_POST['CourseStartWeek'];
        $CourseEndWeek = $_POST['CourseEndWeek'];
        $CourseDayOfWeek = $_POST['CourseDayOfWeek'];
        $CoursePlace = $_POST['CoursePlace'];
        $CourseStartLesson = $_POST['CourseStartLesson'];
        $CourseEndLesson = $_POST['CourseEndLesson'];

        if($CourseEndWeek == null || $CourseDayOfWeek == null || $CourseEndLesson == null || $CourseStartWeek == null || $CourseStartLesson == null || $CourseDayOfWeek == null)
            $this->error('信息不能为空','courseList');

        $db1 = M('courseschedule');
        $db1->courseID = $courseID;
        $db1->CourseStartWeek = $CourseStartWeek;
        $db1->CourseEndWeek = $CourseEndWeek;
        $db1->CourseDayOfWeek = $CourseDayOfWeek;
        $db1->CoursePlace = $CoursePlace;
        $db1->CourseStartLesson = $CourseStartLesson;
        $db1->CourseEndLesson = $CourseEndLesson;
        $db1->where('courseschedule.ID='.$ScheduleID)->save();

        $db = M('course');

        $courseinfo_1 = $db
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$courseID)
            ->getField('course.CourseID,course.CourseName,department.DepartmentName,course.isOpen');

        foreach ($courseinfo_1 as $item){
            $courseinfo['CourseID'] = $item['CourseID'];
            $courseinfo['CourseName'] = $item['CourseName'];
            $courseinfo['DepartmentName'] = $item['DepartmentName'];
            $courseinfo['isOpen'] = $item['isOpen'];
        }
        $this->assign('courseinfo',$courseinfo);

        $courseTeacher = $db
            ->join('courseteacher ON course.CourseID = courseteacher.CourseID')
            ->join('teacher ON teacher.TeaID = courseteacher.TeacherID')
            ->where('course.CourseID='.$courseID)
            ->getField('teacher.TeaName');
        $this->assign('courseTeacher',$courseTeacher);

        $courseSchedule = $db
            ->join('courseschedule ON course.CourseID = courseschedule.CourseID')
            ->where('course.CourseID='.$courseID)
            ->select();
        $this->assign('courseSchedule',$courseSchedule);
        $this->display('AdminCourse/courseInfo');
    }
    
    
    public function courseNewInfo(){
        $term = M('term');
        $nowterm = $term -> where('term.TermStatus = 1') -> select();

        if($nowterm == null)
            $this->error('当前没有已开始学期','courseList');

        $this->display();
    }

    public function addCourse(){

        $term = M('term');
        $nowterm = $term -> where('term.TermStatus = 1') -> select();

        if($nowterm == null)
            $this->error('当前没有已开始学期','courseList');

        foreach ($nowterm as $value){
            $termID = $value['TermID'];
        }

        $CourseName = $_POST['CourseName'];
        $DepartmentID = $_POST['DepartmentID'];
        $CourseTeacherID = $_POST['CourseTeacherID'];
        $CourseStartWeek = $_POST['CourseStartWeek'];
        $CourseEndWeek = $_POST['CourseEndWeek'];
        $CourseDayOfWeek = $_POST['CourseDayOfWeek'];
        $CoursePlace = $_POST['CoursePlace'];
        $CourseStartLesson = $_POST['CourseStartLesson'];
        $CourseEndLesson = $_POST['CourseEndLesson'];

        if($CourseName == null || $CourseDayOfWeek == null || $CourseEndLesson == null || $CourseStartLesson == null || $CourseStartWeek == null
        || $CourseEndWeek == null || $CourseTeacherID == null || $CoursePlace == null)
            $this->error('信息不能为空','courseNewInfo');


        $course = M('course');
        $data['CourseName'] = $CourseName;
        $data['CourseDep'] = $DepartmentID;
        $data['isOpen'] = 1;
        $data['TermID'] = $termID;
        $courseID = $course -> add($data);

        $dbschedule = M('courseschedule');
        $schedule['CourseStartWeek'] = $CourseStartWeek;
        $schedule['CourseEndWeek'] = $CourseEndWeek;
        $schedule['CourseDayOfWeek'] = $CourseDayOfWeek;
        $schedule['CoursePlace'] = $CoursePlace;
        $schedule['CourseStartLesson'] = $CourseStartLesson;
        $schedule['CourseEndLesson'] = $CourseEndLesson;
        $schedule['CourseID'] = $courseID;
        $dbschedule->add($schedule);

        $teacher = M('courseteacher');
        $data1['CourseID'] = $courseID;
        $data1['TeacherID'] = $CourseTeacherID;
        $teacher->add($data1);

        $db = M('course');
        $courseinfo_1 = $db
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$courseID)
            ->getField('course.CourseID,course.CourseName,department.DepartmentName,course.isOpen');

        foreach ($courseinfo_1 as $item){
            $courseinfo['CourseID'] = $item['CourseID'];
            $courseinfo['CourseName'] = $item['CourseName'];
            $courseinfo['DepartmentName'] = $item['DepartmentName'];
            $courseinfo['isOpen'] = $item['isOpen'];
        }
        $this->assign('courseinfo',$courseinfo);

        $courseTeacher = $db
            ->join('courseteacher ON course.CourseID = courseteacher.CourseID')
            ->join('teacher ON teacher.TeaID = courseteacher.TeacherID')
            ->where('course.CourseID='.$courseID)
            ->getField('teacher.TeaName');
        //->select();
        $this->assign('courseTeacher',$courseTeacher);

        $courseStudent = $db
            ->join('coursestudent ON course.CourseID = coursestudent.CourseID')
            ->join('student ON student.StuID = coursestudent.StudentID')
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$courseID)
            ->getField('student.StuID,student.StuName,student.Email,department.DepartmentName,student.Grade');
        //->select();
        $this->assign('courseStudent',$courseStudent);

        $courseSchedule = $db
            ->join('courseschedule ON course.CourseID = courseschedule.CourseID')
            ->where('course.CourseID='.$courseID)
            ->select();
        $this->assign('courseSchedule',$courseSchedule);

        //dump($courseStudent);
        //$this->display('AdminCourse/courseList_1');
        $studentlist = null;
        $this->assign('studentlist',$studentlist);
        $this->display('AdminCourse/studentList');
    }

    public function getClassList(){
        $dep_id = $_POST['dep_id'];
        $grade = $_POST['grade_id'];

        $test = M();
        $sql = 'SELECT depclass.Class FROM depclass WHERE Deptement = '.$dep_id.' AND Grade='.$grade;
        $list = $test ->query($sql);

        $this->ajaxReturn($list,'json');
    }

    public function getDepList(){

        $test = M();
        $sql = 'SELECT * FROM department';
        $list = $test ->query($sql);

        $this->ajaxReturn($list,'json');
    }
    
    public function connectStu(){
        $courseID = $_POST['CourseID'];
        $db = M('course');
        $courseinfo_1 = $db
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$courseID)
            ->getField('course.CourseID,course.CourseName,department.DepartmentName,course.isOpen');

        foreach ($courseinfo_1 as $item){
            $courseinfo['CourseID'] = $item['CourseID'];
            $courseinfo['CourseName'] = $item['CourseName'];
            $courseinfo['DepartmentName'] = $item['DepartmentName'];
            $courseinfo['isOpen'] = $item['isOpen'];
        }
        $this->assign('courseinfo',$courseinfo);

        $courseStudent = $db
            ->join('coursestudent ON course.CourseID = coursestudent.CourseID')
            ->join('student ON student.StuID = coursestudent.StudentID')
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$courseID)
            ->getField('student.StuID,student.StuName,student.Email,department.DepartmentName,student.Grade');
        //->select();
        $this->assign('courseStudent',$courseStudent);

        $courseSchedule = $db
            ->join('courseschedule ON course.CourseID = courseschedule.CourseID')
            ->where('course.CourseID='.$courseID)
            ->select();
        $this->assign('courseSchedule',$courseSchedule);

        $studentlist = null;
        $this->assign('studentlist',$studentlist);
        $this->display('AdminCourse/studentList');
    }

    public function getStu(){
        $dep = $_POST['dep_name'];
        $grade = $_POST['grade_name'];
        $class = $_POST['class_name'];
        $CourseID = $_POST['CourseID'];

        $test = M();
        $sql = 'SELECT student.StuID,student.StuName,student.Grade,student.Email,student.Department,department.DepartmentName FROM student
             JOIN department ON student.Department = department.DepartmentID
             WHERE student.Department ='.$dep.' AND student.Class='.$class.' AND student.Grade ='.$grade.'
             AND student.StuID NOT IN(SELECT coursestudent.StudentID FROM coursestudent WHERE coursestudent.CourseID='.$CourseID.')';
        $studentlist = $test ->query($sql);
        $this->assign('studentlist',$studentlist);

        $db = M('course');
        $courseinfo_1 = $db
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$CourseID)
            ->getField('course.CourseID,course.CourseName,department.DepartmentName,course.isOpen');

        foreach ($courseinfo_1 as $item){
            $courseinfo['CourseID'] = $item['CourseID'];
            $courseinfo['CourseName'] = $item['CourseName'];
            $courseinfo['DepartmentName'] = $item['DepartmentName'];
            $courseinfo['isOpen'] = $item['isOpen'];
        }
        $this->assign('courseinfo',$courseinfo);

        $courseTeacher = $db
            ->join('courseteacher ON course.CourseID = courseteacher.CourseID')
            ->join('teacher ON teacher.TeaID = courseteacher.TeacherID')
            ->where('course.CourseID='.$CourseID)
            ->getField('teacher.TeaName');
        //->select();
        $this->assign('courseTeacher',$courseTeacher);

        $courseStudent = $db
            ->join('coursestudent ON course.CourseID = coursestudent.CourseID')
            ->join('student ON student.StuID = coursestudent.StudentID')
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$CourseID)
            ->getField('student.StuID,student.StuName,student.Email,department.DepartmentName,student.Grade');
        //->select();
        $this->assign('courseStudent',$courseStudent);

        $courseSchedule = $db
            ->join('courseschedule ON course.CourseID = courseschedule.CourseID')
            ->where('course.CourseID='.$CourseID)
            ->select();
        $this->assign('courseSchedule',$courseSchedule);

        $this->display('AdminCourse/studentList');
    }

    public function addstudent(){
        $checkstulist = $_POST['checkStuID'];
        $CourseID = $_POST['CourseID'];
        $coursestudent = M('coursestudent');
        //dump($checkstulist);
        foreach ($checkstulist as $stuID){
            //dump($stuID);
            $data['CourseID'] = $CourseID;
            $data['StudentID'] = $stuID;
            $coursestudent->add($data);
        }
        $db = M('course');

        $courseinfo_1 = $db
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$CourseID)
            ->getField('course.CourseID,course.CourseName,department.DepartmentName,course.isOpen');

        foreach ($courseinfo_1 as $item){
            $courseinfo['CourseID'] = $item['CourseID'];
            $courseinfo['CourseName'] = $item['CourseName'];
            $courseinfo['DepartmentName'] = $item['DepartmentName'];
            $courseinfo['isOpen'] = $item['isOpen'];
        }
        $this->assign('courseinfo',$courseinfo);

        $courseTeacher = $db
            ->join('courseteacher ON course.CourseID = courseteacher.CourseID')
            ->join('teacher ON teacher.TeaID = courseteacher.TeacherID')
            ->where('course.CourseID='.$CourseID)
            ->getField('teacher.TeaName');
        //->select();
        $this->assign('courseTeacher',$courseTeacher);

        $courseStudent = $db
            ->join('coursestudent ON course.CourseID = coursestudent.CourseID')
            ->join('student ON student.StuID = coursestudent.StudentID')
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$CourseID)
            ->getField('student.StuID,student.StuName,student.Email,department.DepartmentName,student.Grade');
        //->select();
        $this->assign('courseStudent',$courseStudent);

        $courseSchedule = $db
            ->join('courseschedule ON course.CourseID = courseschedule.CourseID')
            ->where('course.CourseID='.$CourseID)
            ->select();
        $this->assign('courseSchedule',$courseSchedule);

        $this->display('AdminCourse/studentList');
    }
    private function exportExcel($expName,$expCellName,$expTableData)
    {
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);

        vendor("PHPExcel.Classes.PHPExcel");

        $objPHPExcel = new PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        for($i=0;$i<$cellNum;$i++)
        {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
        }

        for($i=0;$i<$dataNum;$i++)
        {
            for($j=0;$j<$cellNum;$j++)
            {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$expName.'.xls"');
        header('Content-Disposition:attachment;filename="'.$expName.'.xls"');//attachment新窗口打印inline本窗口打印
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function exportStudentExcel()
    {
        $CourseID = $_POST['CourseID'];
        $xlsCellName=array(
            array('CourseID','课程ID'),
            array('StudentID','学号'),
            array('StuName','姓名'),
            array('Department','系别'),
            array('Email','联系方式')
        );

        $test = M();
        $sql = 'SELECT coursestudent.CourseID,coursestudent.StudentID,student.StuName,student.Department,student.Email FROM coursestudent JOIN student on coursestudent.StudentID = student.StuID WHERE coursestudent.CourseID = '.$CourseID;
        $xlsData = $test ->query($sql);

        $this->exportExcel($CourseID.'_学生列表',$xlsCellName,$xlsData);
    }

    public function importStudentExcel()
    {
        if(empty($_FILES))
        {
            $this->error('文件为空','courseList');
        }

        import('ORG.Net.UploadFile');

        $config = array(
            'maxSize'    =>    3145728,
            'savePath'   =>    './MoocPlatform/Modules/Manage/Uploads/',
            'saveRule'   =>    'uniqid',
            'allowExts'  =>    array('jpg', 'png', 'jpeg','doc','docx','xls','xlsx','ppt','pptx','txt'),
            'autoSub'    =>    true,
            'subType'	 =>	   'date',
            'dateFormat'    =>    'Y-m-d',
        );

        $upload = new UploadFile($config);

        $upload->upload();
        $info= $upload->getUploadFileInfo();

        if(!$info)
        {
            $this->error('信息为空','courseList');
        }

        vendor("PHPExcel.Classes.PHPExcel");

        $file_name=$info[0]['savepath'].$info[0]['savename'];
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objPHPExcel = $objReader->load($file_name,$encode='utf-8');
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        // $highestColumn = $sheet->getHighestColumn(); // 取得总列数

        $coursestudent = M('coursestudent');
        for($i=2;$i<=$highestRow;$i++)
        {
            $data['CourseID'] = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
            $data['StudentID'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();

            $flag = $coursestudent ->where('CourseID='.$data['CourseID'].' AND StudentID ='.$data['StudentID'])->select();
            if($flag == null){
                $coursestudent -> add($data);
            }
        }
        unlink($file_name);
        //$this->redirect('/Teacher/someHomework/hwID/'.I('param.hwID'));
        $this->success('导入成功','courseList');
    }
}
?>