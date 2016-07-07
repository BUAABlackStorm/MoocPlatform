<?php

class TeacherAction extends VerifyLoginAction
{
	public function index()
	{
		$teacher=session('teacher');
		$db=M('courseteacher');

		$course_teacher=$db
						->where('courseteacher.TeacherID='.$teacher['TeaID'])
						->join('course ON course.CourseID=courseteacher.CourseID')
						->select();//dump($course_teacher);
		$this->assign('course_teacher',$course_teacher);
						
		$this->display();
	}

	public function personal_info()
	{
		$this->display();
	}

	public function personal_info_mod()
	{
		$this->redirect('/Teacher/personal_info/');
	}

	public function course()
	{
		$teacher=session('teacher');
		// $db1 = M('resource');
		// $resourceList=$db1
		// 			->where('resource.OwnerID='.$teacher['TeaID'].' and resource.CourseID='.I('param.course_id'))
		// 			->select();
		// $this->assign('resourceList',$resourceList);
		//dump(json_encode($resourceList));

		$db=M('course');
		$course=$db
				->where('course.CourseID='.I('param.course_id'))
				->select();
		session('teacher_selected_course',$course[0]);//dump(session('teacher_selected_course')['CourseID']);

		$this->display('course');
	}

	public function ajaxCourse()
	{
		$teacher=session('teacher');
		$db = M('resource');
		$resourceList=$db
					->where('resource.OwnerID='.$teacher['TeaID'].' and resource.CourseID='.session('teacher_selected_course')['CourseID'].' and resource.ResKindID='.I('param.category'))
					->select();

		$this->ajaxreturn($resourceList);
	}

    public function upload()
    {
    	import('ORG.Net.UploadFile');

    	$config = array(
		    'maxSize'    =>    3145728,
		    'savePath'   =>    './MoocPlatform/Modules/MoocPlatform/Uploads/Teacher/',
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
    		$this->redirect('/Teacher/course/course_id/'.session('teacher_selected_course')['CourseID']);
		}
		else
		{
			$teacher = session('teacher');
			$db1 = M('resource');
			$db2 = M('resoucekind');

			$res_kind=$db2
						->where('resoucekind.ResType='.'"'.I('param.category').'"')
						->select();
			$res_kind_id = $res_kind[0]['ResKindID'];

    		foreach($info as $file)
    		{
    			$res=array(
    					'OwnerID'=>$teacher['TeaID'],
    					'CourseID'=>session('teacher_selected_course')['CourseID'],
    					'ResOriginName'=>$file['name'],
    					'ResActualName'=>$file['savename'],
    					'ResPath'=>$file['savepath'],
    					'ResKindID'=>$res_kind_id,
    					'FileSize'=>$file['size']
				);
    			$db1->add($res);
    		}


    		$this->redirect('/Teacher/course/course_id/'.session('teacher_selected_course')['CourseID']);
		}
    }

    public function download()
    {
    	import('ORG.Net.Http');
    	$id_array = I('param.id_array');
    	$db = M('resource');

    	foreach ($id_array as $id)
    	{
    		$res=$db
    			->where('resource.ResID='.$id)
    			->select();

    		Http::download(($res[0]['ResPath']).($res[0]['ResActualName']),urlencode($res[0]['ResOriginName']));
    	}
    }

    public function delete()
    {
    	$id_array = I('param.id_array');

    	$db=M('resource');

    	foreach ($id_array as $id)
    	{
    		$res=$db->where('resource.ResID='.$id)->select();
    		//删除服务器上的文件
    		unlink(($res[0]['ResPath']).($res[0]['ResActualName']));

    		//删除数据库表项
    		$db->where('resource.ResID='.$id)->delete();
    	}

    	$this->redirect('/Teacher/course/course_id/'.session('teacher_selected_course')['CourseID']);
    }

    public function homework()
    {
    	if(!session("?teacher_selected_course"))
    	{
    		$this->redirect('/Teacher/');
    	}

    	$db=M("homework");
    	$course_id=session('teacher_selected_course')['CourseID'];
    	$teacher=session('teacher');
    	$homework=$db
    				->where('homework.CourseID='.$course_id.' and homework.TeaID='.$teacher['TeaID'])
    				->select();

    	$this->assign('homework',$homework);
    	$this->display();
    }

    public function addHomework()
    {
    	$teacher=session('teacher');
    	$db=M("homework");
    	$isGroup=1;

    	if (I('param.isGroup')=='on') 
    	{
    		$isGroup=1;
    	}
    	else
    	{
    		$isGroup=0;
    	}
    	
    	$homework=array(
    				'CourseID'=>session('teacher_selected_course')['CourseID'],
    				'TeaID'=>$teacher['TeaID'],
    				'HwName'=>I('param.homework_name'),
    				'StartDate'=>I('param.start_time'),
    				'EndDate'=>I('param.end_time'),
    				'isGroupHw'=>$isGroup,
    				'Require'=>I('param.require')
    		);
    	$db->add($homework);

    	$this->redirect('/Teacher/course/course_id/'.session('teacher_selected_course')['CourseID']);
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

    public function exportHomeworkExcel()
    {
    	$xlsCellName=array(
	        array('ID','ID'),
	        array('HwID','作业ID'),
	        array('StuID','学号'),
	        array('StuName','姓名'),
	        array('Sex','性别'),
	        array('Department','院系编号'),
	        array('Class','班级'),
	        array('content','作业文本内容'),
	        array('Score','作业分数'),
	        array('Comment','作业评论') 
        );

        $db1=M('hwstu');
        $hwID=I('param.hwID');
        $xlsData=$db1
        		->where('hwstu.HwID='.$hwID)
        		->join('student ON student.StuID=hwstu.StuID')
        		->Field('hwstu.ID,hwstu.HwID,hwstu.StuID,student.StuName,student.Sex,student.Department,student.Class,hwstu.content,hwstu.Score,hwstu.Comment')
        		->select();

        $db2=M('homework');
        $homework=$db2
        			->where('homework.HwID='.$hwID)
        			->select();//dump($homework);

        $this->exportExcel($homework[0]['HwName'],$xlsCellName,$xlsData);
    }
}

?>