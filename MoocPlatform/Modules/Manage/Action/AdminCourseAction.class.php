<?php
/**
 * Created by PhpStorm.
 * User: zf
 * Date: 2016/7/3
 * Time: 19:08
 */

class AdminCourseAction extends Action{

    public function courseList(){
        $Model = M('course');
        $list = $Model->select();
        $this->assign('list',$list);
        $this->display();
    }

    public function courseInfo(){
        $courseID = $_POST['CourseID'];
        $db = M('course');
        $courseTeacher = $db
            ->join('courseteacher ON course.CourseID = courseteacher.CourseID')
            ->join('teacher ON teacher.TeaID = courseteacher.TeacherID')
            ->where('course.CourseID='.$courseID)
            ->select();
        $this->assign('courseTeacher',$courseTeacher);

        $courseStudent = $db
            ->join('coursestudent ON course.CourseID = coursestudent.CourseID')
            ->join('student ON student.StuID = coursestudent.StudentID')
            ->where('course.CourseID='.$courseID)
            ->select();
        $this->assign('courseStudent',$courseStudent);

        $courseSchedule = $db
            ->join('courseschedule ON course.CourseID = courseschedule.CourseID')
            ->where('course.CourseID='.$courseID)
            ->select();
        $this->assign('courseStudent',$courseStudent);

        dump($courseSchedule);
    }

    public function hello(){
        echo 'hello';
    }
}

?>