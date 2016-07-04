<?php

class TeacherAction extends Action
{
	public function index()
	{
		$this->display();
	}

	public function course($id)
	{
		$teacher=session('teacher');

		$db=M('resource');
		$resourceList=$db
					->where('resource.OwnerID='.$teacher->TeaID.' and resource.CourseID='.$id)
					->select();
		$this->assign($resourceList,'resourceList');

		$this->display();
	}

    public function upload()
    {
    	import('ORG.Net.UploadFile');

    	$config = array(
		    'maxSize'    =>    3145728,
		    'savePath'   =>    './MoocPlatform/Modules/MoocPlatform/Uploads/Teacher/',
		    'saveRule'   =>    'uniqid',
		    'allowExts'  =>    array('jpg', 'png', 'jpeg','doc','docx','xls','xlsx','ppt','pptx'),
		    'autoSub'    =>    true,
		    'subType'	 =>	   'date',
		    'dateFormat'    =>    'Y-m-d',
		);

		$upload = new UploadFile($config);

		$upload->upload();
		$info= $upload->getUploadFileInfo();

		if(!$info)
		{
    		$this->error($upload->getError());
		}
		else
		{
			$teacher=session('teacher');
			$db=M('resource');

    		foreach($info as $file)
    		{
    			$res=[
    					'OwnerID'=>$teacher->TeaID,
    					'CourseID'=>I('courseID'),
    					'ResOriginName'=>$file['name'];
    					'ResActualName'=>$file['savename'];
    					'ResPath'=>$file['savepath'];
    				]
    			$db->add($res);
    		}

    		U(MoocPlatform.'/Teacher/course/',['id'=>I('courseID')]);
		}
    }

    public function download()
    {
    	import('ORG.Net.Http');

    	$id_array=I('id_array');
    	$db=M('resource');

    	foreach ($id_array as $id)
    	{
    		$res=$db->find($id);
    		Http::download($res->ResPath,$res->ResOriginName);
    	}
    }
}

?>