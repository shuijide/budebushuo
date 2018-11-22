<?php
/**
 * Created by PhpStorm.
 * User: 闫好闯
 * Date: 2018/10/24
 * Time: 11:28
 */

error_reporting(-1);

require_once '/webService/SaySomeThing/Config/webConfig.php';

require_once '/webService/SaySomeThing/Config/servicesConfig.php';

//swoole项目根目录 绝对路径
define('SWOOLE_PATH',APP_SERVER_PATH.'Swoole/');

########
# Home #
########
define('SWOOLE_HOME_PATH',SWOOLE_PATH.'Admin'); //目录

define('SWOOLE_HOME_NAMESAPCE',ltrim(strrchr(SWOOLE_HOME_PATH,'/'),'/')); // namespaces Home

define('SWOOLE_HOME_SERVICES_FOLDER','Services'); // services 目录

define('SWOOLE_HOME_SERVICES_NAMESPACE',SWOOLE_HOME_NAMESAPCE.'\\'.ltrim(strrchr(__DIR__,'/'),'/').'\\'.SWOOLE_HOME_SERVICES_FOLDER.'\\');

##########
# Models #
##########
define('SWOOLE_HOME_MODELS_PATH',SWOOLE_PATH.'Models'); //目录

define('SWOOLE_HOME_MODELS_NAMESPACE',ltrim(strrchr(SWOOLE_HOME_MODELS_PATH,'/'),'/')); // namespaces Models

###########
# Library #
###########
define('SWOOLE_LIBRARY_PATH',SWOOLE_PATH.'Library'); //目录

define('SWOOLE_LIBRARY_NAMESAPCE',ltrim(strrchr(SWOOLE_LIBRARY_PATH,'/'),'/')); // namespaces Library