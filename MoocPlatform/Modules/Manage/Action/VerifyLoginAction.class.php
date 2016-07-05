<?php
/**
 * Created by PhpStorm.
 * User: Wayne
 * Date: 2016/7/4
 * Time: 14:49
 */

Class VerifyLoginAction extends Action{

    public function _initialize(){
        if(session("?SenID")){
            $this->redirect('/LoginAndRegister/Login');
        }
    }

}

?>