<?php
/**
 * Created by PhpStorm.
 * User: Wayne
 * Date: 2016/7/4
 * Time: 14:47
 */

Class VerifyLoginAction extends Action{

    public function _initialize(){

        if( !session("?student") && !session("?teacher") ){
            $this->redirect('LoginAndRegister/LoginView');
        }
    }

}

?>