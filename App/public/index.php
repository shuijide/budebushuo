<?php
error_reporting(0);

//如果是域名请求转到index.html
if ($_SERVER['REQUEST_URI'] =='/') {

    header('location:http://www.budebushuo.com/index.html');
    exit();
}

//如果访问的是PHP文件 403
if (file_exists('.'.$_SERVER['REQUEST_URI'])) {

    header("HTTP/1.1 403 Forbidden");exit();
}

require_once '/webService/SaySomeThing/Config/webConfig.php';

require_once '/webService/SaySomeThing/Config/servicesConfig.php';

session_start();

setcookie(session_name(),session_id(),LOGIN_EXPIRE,"/");

$app  = new Yaf\Application(APPLICATION_PATH . "/../conf/app.ini");

$app->bootstrap()->run();