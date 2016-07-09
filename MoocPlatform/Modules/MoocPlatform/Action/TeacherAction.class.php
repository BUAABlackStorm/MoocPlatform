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
		$passwd1=I('param.password1');
		$passwd2=I('param.password2');
		$email=I('param.Email');
		$teacher=session('teacher');
		$isSuccessful=2;

		if((""!=$passwd1)||(""!=$passwd2))
		{
			if($passwd1==$passwd2)
			{
				$data['Password']=md5($passwd1);

				M('teacher')->where('teacher.TeaID='.$teacher['TeaID'])
							->save($data);
				$tea=M('teacher')->where('teacher.TeaID='.$teacher['TeaID'])
								 ->select();

				session('teacher',$tea[0]);

				$isSuccessful=1;
			}
			else
			{
				$isSuccessful=0;
			}
		}

		if(($isSuccessful!=0)&&(""!=$email))
		{
			$data['Email']=$email;

			$tea=M('teacher')->where('teacher.TeaID='.$teacher['TeaID'])
					 		 ->select();//dump($tea);dump($email);
			if($tea[0]['Email']!=$email)
			{
				M('teacher')->where('teacher.TeaID='.$teacher['TeaID'])
							->save($data);
				$tea=M('teacher')->where('teacher.TeaID='.$teacher['TeaID'])
					 		 ->select();

				$isSuccessful=1;
				session('teacher',$tea[0]);
			}
		}

		if(1==$isSuccessful)
		{
			session('personal_info_mod_msg',"修改成功!");
		}
		else if(0==$isSuccessful)
		{
			session('personal_info_mod_msg',"修改失败!");
		}
		else if(2==$isSuccessful)
		{
			session('personal_info_mod_msg',null);
		}

		$this->redirect('/Teacher/personal_info/');
	}

	public function teaChat()
	{
		$this->display();
	}

	public function course()
	{
		$teacher=session('teacher');
		$db1 = M('resource');
		$resourceList=$db1
					->where('resource.OwnerID='.$teacher['TeaID'].' and resource.CourseID='.I('param.course_id'))
					->select();
		$this->assign('resourceList',$resourceList);
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
				'saveRule'   =>    'uniqid',
				'allowExts'  =>    array('jpg', 'png', 'jpeg','doc','docx','xls','xlsx','ppt','pptx','txt'),
				'autoSub'    =>    true,
				'subType'	 =>	   'date',
				'dateFormat'    =>    'Y-m-d',
		);

		//若在本地运行
		if($_SERVER['HTTP_HOST'] == "localhost"){
			$config['savePath'] = './MoocPlatform/Modules/MoocPlatform/Uploads/Teacher/';
			$upload = new UploadFile($config);
		}
		//服务器端运行
		else{
			$config['savePath'] = './public/Uploads';
			$upload = new UploadFile($config,'sae');
		}

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

    	if(count($id_array)>1)
    	{
    		import('ORG.Util.FileToZip');

	        $download_file = array();
	        $download_file_name = array();
	        $download_file_count=0;

	        foreach ($id_array as $id)
	        {
	        	$res=$db
					->where('resource.ResID='.$id)
					->select();

				$download_file[$download_file_count] = $res[0]['ResPath'].$res[0]['ResActualName'];
				$download_file_name[$download_file_count++] = $res[0]['ResOriginName'];
	        }

	        $scandir=new traverseDir("","");
	        $scandir->tozip($download_file,$download_file_name);
    	}
    	else
    	{
    		$res=$db
			->where('resource.ResID='.$id_array[0])
			->select();

			//若在本地运行
			if($_SERVER['HTTP_HOST'] == "localhost"){
				Http::download(($res[0]['ResPath']).($res[0]['ResActualName']),urlencode($res[0]['ResOriginName']));
			}
			//服务器端运行
			else{
				$stor = new SaeStorage();
				$url = $stor->getUrl('public', $res[0]['ResPath'] . $res[0]['ResActualName']);
				Header("HTTP/1.1 303 See Other");
				Header("Location: $url");
			}
		}
    }

    public function delete()
    {
    	$id_array = I('param.id_array');

    	$db=M('resource');

    	foreach ($id_array as $id)
    	{
    		$res=$db->where('resource.ResID='.$id)->select();

			//若在本地运行
			if($_SERVER['HTTP_HOST'] == "localhost"){
				//删除服务器上的文件
				unlink(($res[0]['ResPath']).($res[0]['ResActualName']));
			}
			//服务器端运行
			else{
				$stor = new SaeStorage();
				$stor->delete('public', $res[0]['ResPath'] . $res[0]['ResActualName']);
			}

    		//删除数据库表项
    		$db->where('resource.ResID='.$id)->delete();
    	}

    	$this->redirect('/Teacher/course/course_id/'.session('teacher_selected_course')['CourseID']);
    }

    public function homework_index()
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

    public function someHomework()
    {
    	$hwID=I('param.hwID');

    	$db1=M("homework");
    	$some_homework=$db1
    					->where('homework.HwID='.$hwID)
    					->select();//dump($some_homework);

    	$db2=M('hwstu');
    	$student_homework=$db2
    						->where('hwstu.HwID='.$hwID)
    						->join('student ON student.StuID=hwstu.StuID')
    						->Field('hwstu.ID,hwstu.HwID,hwstu.StuID,student.StuName,student.Sex,student.Department,student.Class,hwstu.Content,hwstu.Score,hwstu.Comment')
    						->select();//dump($student_homework);

    	$this->assign('some_homework',$some_homework);
    	$this->assign('student_homework',$student_homework);
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

    public function importHomeworkExcel()
    {
    	if(empty($_FILES))
    	{
    		$this->redirect('/Teacher/someHomework/hwID/'.I('param.hwID'));
    	}

    	import('ORG.Net.UploadFile');

    	$config = array(
		    'maxSize'    =>    3145728,
		    'savePath'   =>    './MoocPlatform/Modules/MoocPlatform/Uploads/Teacher/',
		    'saveRule'   =>    'uniqid',
		    'allowExts'  =>    array('xls','xlsx'),
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
    		$this->redirect('/Teacher/someHomework/hwID/'.I('param.hwID'));
		}

		vendor("PHPExcel.Classes.PHPExcel");

		$file_name=$info[0]['savepath'].$info[0]['savename'];
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objPHPExcel = $objReader->load($file_name,$encode='utf-8');
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        // $highestColumn = $sheet->getHighestColumn(); // 取得总列数

        for($i=2;$i<=$highestRow;$i++)
        {
        	$data['Score'] = $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
        	$data['Comment'] = $objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();

        	M('hwstu')->where('hwstu.ID='.($objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue()))
        			  ->save($data);
        }

        unlink($file_name);

        $this->redirect('/Teacher/someHomework/hwID/'.I('param.hwID'));
    }

    public function changeScore()
    {
    	$ID=I('param.ID');
    	$hwID=I('param.hwID');
    	$db=M('hwstu');

    	$data['Score']=I('param.score');
    	$data['Comment']=I('param.comment');

    	$db->where('hwstu.ID='.$ID)
    	   ->save($data);

    	$this->redirect('/Teacher/someHomework/hwID/'.$hwID);
    }

    public function downloadHomeworkZip()
    {
    	$hwID=I('param.hwID');
    	$stuID=I('param.stuID');
    	$db=M('hwstu');
    	$resource;

    	if(""==$stuID)
    	{
			$resource=$db
    			->where('hwstu.HwID='.$hwID)
    			->join('student ON student.StuID=hwstu.StuID')
    			->join('hwres ON hwres.HwStuID=hwstu.ID')
    			->join('resource ON resource.ResID=hwres.ResID')
    			->select();//dump($resource);
    	}
    	else
    	{
    		$resource=$db
    			->where('hwstu.HwID='.$hwID.' and Hwstu.StuID='.$stuID)
    			->join('student ON student.StuID=hwstu.StuID')
    			->join('hwres ON hwres.HwStuID=hwstu.ID')
    			->join('resource ON resource.ResID=hwres.ResID')
    			->select();//dump($resource);
    	}

    	import('ORG.Util.FileToZip');

        $download_file = array();
        $download_file_name = array();
        $download_file_count=0;

        foreach ($resource as $res)
        {
			$download_file[$download_file_count] = $res['ResPath'].$res['ResActualName'];
			$download_file_name[$download_file_count++] = $res['StuID'].'_'.$res['StuName'].'/'.$res['ResOriginName'];
        }

        $scandir=new traverseDir("","");
        $scandir->tozip($download_file,$download_file_name);
    }

    private function exportGroupExcel($expName,$expCellName,$expTableData1,$expTableData2)
    {
    	$cellNum = count($expCellName);
    	$expTableDataNum1 = count($expTableData1);

    	vendor("PHPExcel.Classes.PHPExcel");

    	$objPHPExcel = new PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        for($i=0;$i<$cellNum;$i++)
        {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
        }

        $amount=2;
        for($i=0;$i<$expTableDataNum1;$i++)
        {
        	$eachGroupNumber=count($expTableData2[$i]);

        	for ($j=0; $j <=4; $j++)
        	{
        		$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].$amount, $expTableData1[$i][$expCellName[$j][0]]);
        	}
        	for ($j=10; $j <$cellNum; $j++)
        	{
        		$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].$amount, $expTableData1[$i][$expCellName[$j][0]]);
        	}

        	for ($j=0; $j < $eachGroupNumber; $j++)
        	{ 
        		for ($k=5; $k < 10; $k++)
	        	{
					$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$k].$amount, $expTableData2[$i][$j][$expCellName[$k][0]]);
	        	}

	        	$amount++;
	        }

	        for ($j=0; $j <=4; $j++)
	        { 
	        	$objPHPExcel->getActiveSheet(0)->mergeCells($cellName[$j].($amount-$eachGroupNumber).':'.$cellName[$j].($amount-1));

				$objPHPExcel->getActiveSheet()->getStyle($cellName[$j].($amount-$eachGroupNumber))->getAlignment()
				->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	        }

	        for ($j=10; $j < $cellNum; $j++)
	        { 
	        	$objPHPExcel->getActiveSheet(0)->mergeCells($cellName[$j].($amount-$eachGroupNumber).':'.$cellName[$j].($amount-1));

				$objPHPExcel->getActiveSheet()->getStyle($cellName[$j].($amount-$eachGroupNumber))->getAlignment()
				->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	        }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$expName.'.xls"');
        header('Content-Disposition:attachment;filename="'.$expName.'.xls"');//attachment新窗口打印inline本窗口打印
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
        $objWriter->save('php://output'); 
        exit; 
    }

    public function exportGroupHomeworkExcel()
    {
    	$xlsCellName=array(
	        array('GroupID','小组编号'),
	        array('GroupName','小组名称'),
	        array('ID','ID'),
	        array('HwID','作业ID'),
	        array('PrincipalID','组长学号'),
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
        $db2=M('learninggroup');
        $db3=M('homework');
        $hwID=I('param.hwID');
        $group_count=0;
        $group_member=array();

        $xlsData1=$db1
        		->where('hwstu.HwID='.$hwID)
        		->join('student ON student.StuID=hwstu.StuID')
        		->join('learninggroup ON learninggroup.PrincipalID=hwstu.StuID')
        		->Field('hwstu.ID,hwstu.HwID,hwstu.StuID,student.StuName,student.Sex,student.Department,student.Class,hwstu.content,hwstu.Score,hwstu.Comment,learninggroup.GroupID,learninggroup.GroupName,learninggroup.PrincipalID')
        		->select();//dump($xlsData1);
        $group_count=count($xlsData1);

        for($i=0;$i<$group_count;$i++)
        { 
        	$xlsData2=$db2
        			->where('learninggroup.PrincipalID='.$xlsData1[$i]['StuID'])
        			->join('groupstu ON groupstu.GroupID=learninggroup.GroupID')
        			->join('student ON student.StuID=groupstu.StudentID')
        			->Field('student.StuID,student.StuName,student.Sex,student.Department,student.Class')
        			->select();
        	$group_member[$i]=$xlsData2;
        }//dump($group_member[0]);

        $homework=$db3
        			->where('homework.HwID='.$hwID)
        			->select();//dump($homework);

        $this->exportGroupExcel($homework[0]['HwName'],$xlsCellName,$xlsData1,$group_member);
    }

    public function importGroupHomeworkExcel()
    {
    	if(empty($_FILES))
    	{
    		$this->redirect('/Teacher/someHomework/hwID/'.I('param.hwID'));
    	}

    	import('ORG.Net.UploadFile');

    	$config = array(
		    'maxSize'    =>    3145728,
		    'savePath'   =>    './MoocPlatform/Modules/MoocPlatform/Uploads/Teacher/',
		    'saveRule'   =>    'uniqid',
		    'allowExts'  =>    array('xls','xlsx'),
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
    		$this->redirect('/Teacher/someHomework/hwID/'.I('param.hwID'));
		}

		vendor("PHPExcel.Classes.PHPExcel");

		$file_name=$info[0]['savepath'].$info[0]['savename'];
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objPHPExcel = $objReader->load($file_name,$encode='utf-8');
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        // $highestColumn = $sheet->getHighestColumn(); // 取得总列数

        for($i=2;$i<=$highestRow;$i++)
        {
        	if(""!=$objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue())
        	{
        		$data['Score'] = $objPHPExcel->getActiveSheet()->getCell("L".$i)->getValue();
	        	$data['Comment'] = $objPHPExcel->getActiveSheet()->getCell("M".$i)->getValue();

	        	M('hwstu')->where('hwstu.ID='.($objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue()))
	        			  ->save($data);
	        }
        }

        unlink($file_name);

        $this->redirect('/Teacher/someHomework/hwID/'.I('param.hwID'));
    }
}

?>

