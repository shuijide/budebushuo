<?php

//swoole配置文件
require_once './config.php';

$loader =require_once '../../vendor/autoload.php';

//命名空间注册
$loader->addPsr4(SWOOLE_HOME_NAMESAPCE.'\\', SWOOLE_HOME_PATH); // \Home
$loader->addPsr4(SWOOLE_HOME_MODELS_NAMESPACE.'\\', SWOOLE_HOME_MODELS_PATH); // \Models
$loader->addPsr4(SWOOLE_LIBRARY_NAMESAPCE.'\\', SWOOLE_LIBRARY_PATH); // \Library

$swooleServer =new \Library\Server\server(/*127.0.0.1*/WEB_SWOOLE_HOST,/*9501*/WEB_User_SERVICES);

$swooleServer->setServicesNamespace(SWOOLE_HOME_SERVICES_NAMESPACE);

$swooleServer->start();