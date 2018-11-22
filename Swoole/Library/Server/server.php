<?php
/**
 * Created by PhpStorm.
 * User: 闫好闯
 * Date: 2018/10/29
 * Time: 14:44
 */
namespace Library\Server;
class server{

    protected $host ='127.0.0.1'; //host

    protected $port =9501; //port

    protected $server; //swoole 服务对象

    protected $namespace =NULL; //启动的服务

    protected $serialNumber =NULL; //swoole 客户端IP

    protected $startTime =0; //建立链接时间

    protected $costTime =0; //本次访问花费的时间

    public function __construct($swooleHost, $swoolePort)
    {
        $this->host =$swooleHost;
        $this->port =$swoolePort;

        register_shutdown_function(array($this, 'getLastError'));

        $this->create();

        $this->connect();

        $this->receive();

        $this->close();

        return $this;
    }

    //设置服务命名空间
    public function setServicesNamespace($namespace)
    {
        if (empty($namespace)) {
            exit('Namespace can not be empty , please use function setServicesNamespace() setting ............'.PHP_EOL);
        }

        $this->namespace =$namespace;
    }

    //启动服务器
    public function start()
    {
        if (is_null($this->namespace)) {
            exit('Namespace can not be empty ...............'.PHP_EOL);
        }

        echo 'start on '.$this->server->host.':'.$this->server->port.' ...............'.PHP_EOL;

        if (!$this->server->start()) {

            exit('Services Startup failed ...............'.PHP_EOL);
        }
    }

    //创建服务
    protected function create()
    {
       if (!extension_loaded('swoole')) {

           exit('Swoole extension does not exist ...............'.PHP_EOL);
       }

        $this->server = new \swoole_server($this->host, $this->port);
    }

    //监听连接进入事件
    protected function connect()
    {
        $this->server->on('connect', function ($serv, $fd) {

            $this->startTime =microtime(TRUE);

            echo "Client: Connect ".date('H:i:s').PHP_EOL;

        });
    }

    //监听数据接收事件
    protected function receive()
    {
        $this->server->on('receive', function ($serv, $fd, $from_id, $data) {

            try{
                echo $data.PHP_EOL;

                $this->serialNumber =$fd;

                $clientData =unserialize($data);

                //请求的xxxServices.php文件（类）
                if (empty($clientData['services'])) {

                    throw new \Exception('services is not allow empty');

                }
                //请求的xxxServices.php文件（类）中的方法
                if (empty($clientData['function'])) {

                    throw new \Exception('function is not allow empty');
                }
                // 发送到 servicesXXX.php文件（类）中的方法的 参数
                if ($clientData['params'] ==NULL) {
                    $param =[];
                }else{
                    $param =$clientData['params'];
                }

                //返回值 不支持资源类型数据 仅访问静态方法
                $returnData = call_user_func_array(array($this->namespace.$clientData['services'],$clientData['function']),$param);

                $this->send($returnData);

            }catch (\Exception $exception){

                $code =$exception->getCode();

                if (empty($code)) {
                    $code =101;
                }

                $this->sendError($exception->getMessage(),$code);
            }
        });
    }

    //数据返回 序列化数据
    protected function send($returnData)
    {
        $this->server->send($this->serialNumber, serialize($returnData));
    }

    //错误信息返回
    protected function sendError($message, $code = 505)
    {
        $errorData =[
            'SwooleMsg'=>$message,
            'SwooleCode'=>$code
        ];

        $this->send($errorData);
    }

    //监听连接关闭事件
    protected function close()
    {
        $this->server->on('close', function ($serv, $fd) {

            $this->costTime =(microtime(TRUE) - $this->startTime) * 1000;

            echo "cost: [".round($this->costTime,1).'ms]'.PHP_EOL.PHP_EOL;
        });
    }

    // 注册捕捉错误的方法
    protected function getLastError()
    {
        $error =error_get_last();

        if (!is_null($error)) {

            $message =rtrim(strstr($error['message'],'#1',TRUE),PHP_EOL);

            $this->sendError($message);
        }
    }
}