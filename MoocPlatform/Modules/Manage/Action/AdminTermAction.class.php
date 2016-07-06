<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2016/7/5
 * Time: 9:52
 */
class AdminTermAction extends Action{

    public function getTermList(){
        $term = M('term');
        $termlist = $term -> order('TermID desc') -> select();
        $this -> assign('termlist',$termlist);
        $this -> display('AdminTerm/getTermList');
    }

    public function endTerm(){
        $TermID = $_POST['TermID'];
        $Term = M('term');
        $data['TermStatus'] = 0;
        $Term -> where('term.TermID='.$TermID)->save($data);

        $Course = M('course');
        $updatecourse['isOpen'] = 0;
        $Course -> where('course.TermID='.$TermID)->save($updatecourse);
        $this->getTermList();
    }
    
    public function newTerm(){
        $term = M('term');
        $nowterm = $term -> where('term.TermStatus = 1') -> select();

        if($nowterm != null)
            $this->error('当前已有开始学期，无法创建新学期','getTermList');
        
        $this -> display('AdminTerm/preferences');
    }
    
    public function addTerm(){
        $termBegin = $_POST['termbegin'];
        $termEnd = $_POST['termend'];
        $termweeknum = $_POST['times'];
        $termname = $_POST['termname'];

        //dump($termBegin);

        if($termBegin == null || $termEnd == null ||$termweeknum == null || $termname == null)
            $this->error('信息不能为空','AdminTerm/newTerm');

        $term = M('term');
        $data['TermName'] = $termname;
        $data['TermWeeks'] = $termweeknum;
        $data['TermStart'] = $termBegin;
        $data['TermEnd'] = $termEnd;
        $data['TermStatus'] = 1;
        $term -> add($data);

        $this->success('新增学期成功', 'getTermList');
    }

    
}
?>