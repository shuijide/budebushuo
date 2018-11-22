<?php

use Yaf\Bootstrap_Abstract;
use Yaf\Application;
use Yaf\Registry;
use Yaf\Dispatcher;

class Bootstrap extends Bootstrap_Abstract {

    public function _initConfig(Dispatcher $dispatcher) {
        //异常设置
        //异常抛出模式
        $dispatcher->throwException(TRUE);
        $dispatcher->catchException(TRUE);

		//把配置保存起来
		$arrConfig = Application::app()->getConfig();
		Registry::set('config', $arrConfig);
	}

	public function _initPlugin(Dispatcher $dispatcher) {

        $formVerify = new formVerifyPlugin();
        $dispatcher->registerPlugin($formVerify);
	}

	//配置路由验证
	public function _initRoute(Dispatcher $dispatcher) {

        $baseRequest =$dispatcher->getInstance()->getRequest();

        if (!empty($baseRequest) && mb_strlen($baseRequest->getRequestUri(),'UTF-8') > 1) {

            $uriExp =explode('/',$baseRequest->getRequestUri());

            $router =new \Sroute\route(); //引入路由文件 路由验证 过滤

            //不允许一个参数的uri
            if (count($uriExp) < 3) {
                $router->locationIndex();
            }

            $controller = strtolower($uriExp[1]);
            $method     = $baseRequest->getMethod();
            $action     = $uriExp[2];

            if (isset($router->route[$controller][$method]) ==FALSE) {

                $router->locationIndex();
            }

            switch ($method) :
                case 'GET':
                    if (!in_array(strtolower($action),$router->route[$controller][$method])) {

                        $router->locationIndex();
                    }
                    break;
                case 'POST':
                    if (!in_array(strtolower($action),$router->route[$controller][$method])) {

                        $router->locationIndex();
                    }
                    break;
                default:
                    $router->locationIndex();
                    break;
            endswitch;
        }
	}
	
	public function _initView(Dispatcher $dispatcher) {

		//在这里注册自己的view控制器，例如smarty,firekylin
        $dispatcher->disableView(); //关闭自动视图
    }
}