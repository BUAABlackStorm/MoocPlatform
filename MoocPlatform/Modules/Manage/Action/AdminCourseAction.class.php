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
            ->getField('course.CourseID,course.CourseName,department.DepartmentName');
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
        $db_1 = M('coursestudent');
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

        $studentlist = null;
        $this->assign('studentlist',$studentlist);
        $this->display('AdminCourse/courseInfo');
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
        $db = M('courseschedule');
        $db->courseID = $courseID;
        $db->CourseStartWeek = $CourseStartWeek;
        $db->CourseEndWeek = $CourseEndWeek;
        $db->CourseDayOfWeek = $CourseDayOfWeek;
        $db->CoursePlace = $CoursePlace;
        $db->CourseStartLesson = $CourseStartLesson;
        $db->CourseEndLesson = $CourseEndLesson;
        $db->where('courseschedule.ID='.$ScheduleID)->save();

        $this->courseList();
    }
    
    
    public function courseNewInfo(){
        $this->display();
    }

    public function addCourse(){

        $CourseName = $_POST['CourseName'];
        $DepartmentID = $_POST['DepartmentID'];
        $CourseTeacherID = $_POST['CourseTeacherID'];
        $CourseStartWeek = $_POST['CourseStartWeek'];
        $CourseEndWeek = $_POST['CourseEndWeek'];
        $CourseDayOfWeek = $_POST['CourseDayOfWeek'];
        $CoursePlace = $_POST['CoursePlace'];
        $CourseStartLesson = $_POST['CourseStartLesson'];
        $CourseEndLesson = $_POST['CourseEndLesson'];

        $course = M('course');
        $data['CourseName'] = $CourseName;
        $data['CourseDep'] = $DepartmentID;
        $data['isOpen'] = 1;
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
        $this->display('AdminCourse/courseInfo');
    }

    public function getClassList(){
        
        //$this->ajaxReturn($data,'json');
    }

    public function getStu(){
        $dep = $_POST['s1'];
        $grade = $_POST['s2'];
        $class = $_POST['s3'];
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

        $this->display('AdminCourse/courseInfo');
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

        $this->display('AdminCourse/courseInfo');
    }
    public function hello(){
        echo 'Success';
    }
    public function test(){
        echo 'hello';
        $this->display('AdminCourse/courseList_1');
    }
}

?>