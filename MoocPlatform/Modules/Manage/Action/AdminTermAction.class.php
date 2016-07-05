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
        $this -> display('AdminTerm/preferences');
    }

    
}
?>