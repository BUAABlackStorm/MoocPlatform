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

	public function course($course_id)
	{
		$teacher=session('teacher');
		$db1 = M('resource');
		$resourceList=$db1
					->where('resource.OwnerID='.$teacher['TeaID'].' and resource.CourseID='.$course_id)
					->select();
		$this->assign('resourceList',$resourceList);
		//dump(json_encode($resourceList));

		$db2=M('course');
		$course=$db2
				->where('course.CourseID='.$course_id)
				->select();
		$this->assign('course',$course);

		$this->display('course');
	}

	public function ajaxCourse($course_id)
	{
		$teacher=session('teacher');
		$db = M('resource');
		$resourceList=$db
					->where('resource.OwnerID='.$teacher['TeaID'].' and resource.CourseID='.$course_id.' and resource.ResKindID='.I('param.category'))
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
    		$this->redirect('/Teacher/course/course_id/'.I('param.course_id'));
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
    					'CourseID'=>I('param.course_id'),
    					'ResOriginName'=>$file['name'],
    					'ResActualName'=>$file['savename'],
    					'ResPath'=>$file['savepath'],
    					'ResKindID'=>$res_kind_id,
    					'FileSize'=>$file['size']
				);
    			$db1->add($res);
    		}


    		$this->redirect('/Teacher/course/course_id/'.I('param.course_id'));
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

    	$this->redirect('/Teacher/course/course_id/'.I('param.course_id'));
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
    				'CourseID'=>I('param.course_id'),
    				'TeaID'=>$teacher['TeaID'],
    				'HwName'=>I('param.homework_name'),
    				'StartDate'=>I('param.start_time'),
    				'EndDate'=>I('param.end_time'),
    				'isGroupHw'=>$isGroup,
    				'Require'=>I('param.require')
    		);
    	$db->add($homework);

    	$this->redirect('/Teacher/course/course_id/'.I('param.course_id'));
    }
}

?>