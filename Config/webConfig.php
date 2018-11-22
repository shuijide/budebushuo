<?php
/**
 * Created by PhpStorm.
 * User: 闫好闯
 * Date: 2018/10/29
 * Time: 9:53
 */

//项目根目录
define('APP_SERVER_PATH',dirname(__DIR__).'/');
//swoole host
define('WEB_SWOOLE_HOST','127.0.0.1');

define('DB_HOST','127.0.0.1');
define('DB_USER','phpuser');
define('DB_PASS','123456');
define('DB_NAME','shuijide2018');

define('PAGE_SIZE',3); //页码起始页为1

define("APPLICATION_PATH",  APP_SERVER_PATH.'App');

define('LOGIN_EXPIRE',time() + 3600 * 4); //定义登录过期时间