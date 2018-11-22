<?php
/**
 * Created by PhpStorm.
 * User: YHC
 * Date: 2018/11/12
 * Time: 10:08
 */

namespace Application;

class ControllerAdmin extends ControllerBase{

    protected $swooleLink =NULL;
    //构造方法
    public function init()
    {
        parent::init();

//        if (empty($_SESSION['code'])) {
//            header('location:http://www.budebushuo.com/index.html');
//        }
//
//        $controller =$this->getRequest()->getControllerName();
//        $action =$this->getRequest()->getActionName();
//
//        //用于验证当前用户是否有操作权限
//        $this->swooleLink =$this->swooleServer(WEB_Admin_SERVICES,'adminServices');
//
//        $data = $this->swooleLink->grantVeryfy($_SESSION['code'],$controller,$action);
//
//        if ($data == false) {
//            header('location:http://www.budebushuo.com/index.html');
//        }
    }
}