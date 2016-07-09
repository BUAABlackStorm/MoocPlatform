<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2016/7/8
 * Time: 9:01
 */
class AdminInfoAction extends Action
{

    public function beginsearch()
    {
        $db = M('department');
        $deplist = $db->select();
        $this->assign('deplist', $deplist);
        $studentlist = null;
        $this->assign('studentlist', $studentlist);
        //dump($deplist);
        $this->display('AdminInfo/studentList');
    }

    public function studentList()
    {

        $depID = $_POST['depID'];
        $dep = M('department');
        $deplist = $dep->select();
        $this->assign('deplist', $deplist);

        $db = M('student');
        $studentlist = $db->join('department ON student.Department = department.DepartmentID')
            ->where('student.Department=' . $depID)
            ->getField('student.StuID,department.DepartmentName,student.StuName,student.Sex,student.Email,student.Department,student.Class,student.Grade');
        //dump($studentlist);
        $this->assign('studentlist', $studentlist);
        $this->display('AdminInfo/studentList');

    }

    public function studentInfo()
    {
        $StuID = $_POST['StuID'];
        //dump($StuID);
        $db = M('student');
        $studentInfo_1 = $db->join('department ON student.Department = department.DepartmentID')
            ->where('student.StuID=' . $StuID)
            ->getField('student.StuID,department.DepartmentName,student.StuName,student.Sex,student.Email,student.Department,student.Class,student.Grade');

        foreach ($studentInfo_1 as $info) {
            $studentInfo['StuID'] = $info['StuID'];
            $studentInfo['DepartmentName'] = $info['DepartmentName'];
            $studentInfo['StuName'] = $info['StuName'];
            $studentInfo['Sex'] = $info['Sex'];
            $studentInfo['Email'] = $info['Email'];
            $studentInfo['Department'] = $info['Department'];
            $studentInfo['Class'] = $info['Class'];
            $studentInfo['Grade'] = $info['Grade'];
        }
        $this->assign('studentInfo', $studentInfo);
        $this->display('AdminInfo/studentInfo');
    }

    public function editStudent()
    {
        $StuID = $_POST['StuID'];
        $data['StudentName'] = $_POST['StudentName'];
        $data['StudentSex'] = $_POST['StudentSex'];
        $data['StudentDep'] = $_POST['StudentDep'];
        $data['StudentClass'] = $_POST['StudentClass'];
        $data['StudentGrade'] = $_POST['StudentGrade'];
        $data['StudentEmail'] = $_POST['StudentEmail'];
        //dump($data);
        //判断
        if ($data['StudentName'] == null || $data['StudentSex'] == null || $data['StudentDep'] == null || $data['StudentClass'] == null || $data['StudentGrade'] == null || $data['StudentEmail'] == null)
            $this->error('信息不能为空', 'beginsearch');
        $stu = M('student');

        $stu->where('student.StuID=' . $StuID)->save($data);
        $this->success('修改成功', 'beginsearch');
    }

    public function searchTeacher()
    {
        $db = M('department');
        $deplist = $db->select();
        $this->assign('deplist', $deplist);
        $studentlist = null;
        $this->assign('studentlist', $studentlist);
        //dump($deplist);
        $this->display('AdminInfo/teacherList');
    }

    public function teacherList()
    {

        $depID = $_POST['depID'];
        $dep = M('department');
        $deplist = $dep->select();
        $this->assign('deplist', $deplist);

        $db = M('teacher');
        $teacherlist = $db->join('department ON teacher.Department = department.DepartmentID')
            ->where('teacher.Department=' . $depID)
            ->getField('teacher.TeaID,department.DepartmentName,teacher.TeaName,teacher.Sex,teacher.Email,teacher.Department');
        //dump($teacherlist);
        $this->assign('teacherlist', $teacherlist);
        $this->display('AdminInfo/teacherList');

    }

    public function teacherInfo()
    {
        $TeaID = $_POST['TeaID'];
        //dump($TeaID);
        $db = M('teacher');
        $teacherInfo_1 = $db->join('department ON teacher.Department = department.DepartmentID')
            ->where('teacher.TeaID=' . $TeaID)
            ->getField('teacher.TeaID,department.DepartmentName,teacher.TeaName,teacher.Sex,teacher.Email,teacher.Department');

        foreach ($teacherInfo_1 as $info) {
            $teacherInfo['TeaID'] = $info['TeaID'];
            $teacherInfo['DepartmentName'] = $info['DepartmentName'];
            $teacherInfo['TeaName'] = $info['TeaName'];
            $teacherInfo['Sex'] = $info['Sex'];
            $teacherInfo['Email'] = $info['Email'];
            $teacherInfo['Department'] = $info['Department'];
        }
        $this->assign('teacherInfo', $teacherInfo);
        //dump($teacherInfo);
        $this->display('AdminInfo/teacherInfo');
    }

    public function editTeacher()
    {
        $TeaID = $_POST['TeaID'];
        $data['TeacherName'] = $_POST['TeacherName'];
        $data['TeacherSex'] = $_POST['TeacherSex'];
        $data['TeacherDep'] = $_POST['TeacherDep'];
        $data['TeacherEmail'] = $_POST['TeacherEmail'];
        //dump($data);
        //判断
        if ($data['TeacherName'] == null || $data['TeacherSex'] == null || $data['TeacherDep'] == null || $data['TeacherEmail'] == null)
            $this->error('信息不能为空', 'searchTeacher');
        $stu = M('teacher');

        $stu->where('teacher.TeaID=' . $TeaID)->save($data);
        $this->success('修改成功', 'searchTeacher');
    }
}
?>