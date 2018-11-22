<?php

use Application\ControllerBase;

class IndexController extends ControllerBase {

    public function indexAction()
    {
        echo strtotime('2018-07-16 00:00:00');
        echo '<br>';
        echo strtotime('2018-07-23 00:00:00');

        $this->display('index',['name'=>'display']);
    }

    //登录
    public function loginAction()
    {
        try{

            if (isset($_SESSION['code']) && !empty($_SESSION['code'])) {
                $this->sendContent('已登录');
            }

            $params =$this->params;

            $must =['username','password'];

            if (!formVerifyPlugin::keyExist($params,$must) || !formVerifyPlugin::checkEmpty($params,$must)) {
                formVerifyPlugin::thowExc('参数错误');
            }
            //登录名特殊符号
            if (!formVerifyPlugin::accountLimit($params['username']) || formVerifyPlugin::specialSymbol($params['username'])) {
                formVerifyPlugin::thowExc('登录名格式错误');
            }
            //密码
            if (!formVerifyPlugin::checkPassword($params['password'])) {
                formVerifyPlugin::thowExc('密码格式错误');
            }

            //使用swoole服务
            $link =$this->swooleServer(WEB_User_SERVICES,'userServices');

            $data = $link->verifyAccount($params['username'],$params['password'],$_SERVER['REMOTE_ADDR']);

            switch ($data) {
                case 'USER_ERROR':
                case 'PASS_ERROR':
                    formVerifyPlugin::thowExc('账号或密码错误');
                    break;
                case 'SYSTEM_ERROR':
                    formVerifyPlugin::thowExc('网络异常，请稍后');
                default:
                    break;
            }

            $_SESSION['code'] =$data['code'];
            formVerifyPlugin::setCookie('code',$data['code']);

            $this->sendContent($data);

        }catch (\Exception $e){
            $this->setCode(ERROR_CODE);
            $this->setMessage($e->getMessage());
            $this->sendContent();
        }
    }

    //退出登录
    public function logoutAction()
    {
        try{
            //清除cooker
            formVerifyPlugin::clearCookie(['code']);
            //清除session
            $_SESSION = array();
            session_destroy();

        }catch (\Exception $e){

        }

        $this->sendContent();
    }

    //导航分类
    public function articleCateAction()
    {
        try{
            //使用swoole服务
            $link =$this->swooleServer(WEB_Index_SERVICES,'indexServices');

            $code =empty($_SESSION['code']) ? '' : $_SESSION['code'];

            $data = $link->navArticleCate($code);

            $this->sendContent($data);

        }catch (\Exception $e){
            $this->setCode(ERROR_CODE);
            $this->setMessage($e->getMessage());
            $this->sendContent();
        }
    }
}