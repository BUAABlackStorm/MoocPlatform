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
        $stuID = session('student')['StuID'];

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
        $stuID = session('student')['StuID'];

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
        $stuID = session('student')['StuID'];
        //p($stuID);

        session('selectedHwID', I('HwID'));
        //p(session('selectedHwID'));
        
        $homework = M('Homework')->where(array("HwID" => I('HwID')))->find();
        $this->homework = $homework;
        
        $condition['StuID'] = $stuID;
        $condition['HwID'] = I('HwID');

        $hwstu = M('Hwstu')->where($condition)->find();
        $this->hwstu = $hwstu;
        //p($hwstu);

        session('selectedHwstuID', $hwstu['ID']);

        //p(session('selectedHwstuID'));
        
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
        
        M('Resource')->where(array("ResID" => I('ResID')))->delete();
        M('Hwres')->where(array("ResID" => I('ResID')))->delete();
        
        $this->redirect('Student/submithomework', array('HwID' => session('selectedHwID')), 1, '页面跳转中...');
    }

    public function upload() {

        $stuID = session('student')['StuID'];

        import('ORG.Net.UploadFile');
        $config = array(
            'maxSize'    =>    3145728,
            'savePath'   =>    './MoocPlatform/Modules/MoocPlatform/Uploads/',
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
            //$this->error($upload->getError());
            $this->redirect('Student/submithomework', array('HwID' => session('selectedHwID')), 1, '页面跳转中...');
        }
        else
        {
            $db1=M('resource');
            //$db2=M('resoucekind');

//            $res_kind=$db2
//                ->where('resoucekind.ResType='.'"'.I('param.category').'"')
//                ->select();
//            $res_kind_id=$res_kind[0]['ResKindID'];

            //dump(session('selectedHwID'));
            $condition['StuID'] = $stuID;
            $condition['HwID'] = session('selectedHwID');
            $hwstu = M('Hwstu')->where($condition)->find();
            $this->hwstu = $hwstu;

            $hwstuID = $hwstu['ID'];


            // 第一次提交
            if ($hwstu['ID'] == '')
            {
                $hs = array(
                    'HwID' => session('selectedHwID'),
                    'StuID' => $stuID,
                );
                $hwstuID = M('Hwstu')->add($hs);
            }

            //p($hwstuID);

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
                $result = $db1->add($res);

                $hr = array(
                    'HwStuID' => $hwstuID,
                    'ResID' => $result,
                );
                //dump($hr);
                M('Hwres')->add($hr);
            }



            $this->redirect('Student/submithomework', array('HwID' => session('selectedHwID')), 1, '页面跳转中...');
        }
    }

}
?>

