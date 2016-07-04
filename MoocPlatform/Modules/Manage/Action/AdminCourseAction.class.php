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
        $this->display();
    }

    public function courseInfo(){
        $courseID = $_POST['CourseID'];
        $db = M('course');

        $courseinfo = $db
            ->join('department ON course.CourseDep = department.DepartmentID')
            ->where('course.CourseID='.$courseID)
            ->getField('course.CourseID,course.CourseName,department.DepartmentName,course.isOpen');
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

        //dump($courseSchedule);
        //$this->display('AdminCourse/courseList_1');
        $this->display();
    }

    public function courseNewInfo(){
        $this->display();
    }
}

?>