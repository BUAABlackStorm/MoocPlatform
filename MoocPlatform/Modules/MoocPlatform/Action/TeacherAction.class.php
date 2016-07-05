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
						->select();
		$this->assign('course_teacher',$course_teacher);
						
		$this->display();
	}

	public function course($course_id)
	{
		$teacher=session('teacher');
		$db = M('resource');
		$resourceList=$db
					->where('resource.OwnerID='.$teacher['TeaID'].' and resource.CourseID='.$course_id)
					->select();
		$this->assign('resourceList',$resourceList);
		$this->assign('course_id',$course_id);

		$this->display('index');
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
			$teacher=session('teacher');
			$db=M('resource');

    		foreach($info as $file)
    		{
    			$res=[
    					'OwnerID'=>$teacher['TeaID'],
    					'CourseID'=>I('param.course_id'),
    					'ResOriginName'=>$file['name'],
    					'ResActualName'=>$file['savename'],
    					'ResPath'=>$file['savepath'],
    				];
    			$db->add($res);
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
    	$course_id = I('param.course_id');

    	$db=M('resource');

    	foreach ($id_array as $id)
    	{
    		$res=$db->where('resource.ResID='.$id);
    		$res_tmp=$res->select();
    		//删除服务器上的文件
    		unlink(($res[0]['ResPath']).($res[0]['ResActualName']));

    		//删除数据库表项
    		$res->delete();
    	}

    	$this->redirect('/Teacher/course/course_id/'.I('param.course_id'));
    }

    public function addHomework()
    {
    	
    }
}

?>