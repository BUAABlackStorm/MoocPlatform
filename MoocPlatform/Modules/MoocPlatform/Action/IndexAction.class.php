<?php
/**
 * Created by PhpStorm.
 * User: Wayne
 * Date: 2016/7/2
 * Time: 16:18
 */
class IndexAction extends VerifyLoginAction{

    public function index(){

        $user = M('User')->select();
        p($user);
        $this->display();
    }
}

?>