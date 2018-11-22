<?php
/**
 * Created by PhpStorm.
 * User: 闫好闯
 * Date: 2018/10/26
 * Time: 16:26
 */

namespace Library\Server;

class client{

    //错误码 90x

    protected $host ='127.0.0.1';

    protected $servser =9501; //swoole的服务端口(服务名字)

    protected $client =NULL; //swoole 服务

    protected $services =NULL; //请求swoole中的类名称

    protected $function =NULL; //请求swoole中的类的方法名称

    protected $param =NULL; //向swoole 服务端发送的数据

    protected $content =NULL; //swoole服务返回的数据

    public function __construct($serverName, $servicesName)
    {
        if (empty($serverName)) {
            throw new \Exception('serverName不能为空',909);
        }

        if (empty($servicesName)) {
            throw new \Exception('servicesName不能为空',909);
        }

        $this->servser =$serverName;

        $this->services =$servicesName;

        return $this->swooleConnect();
    }

    public function __call($callFunction,$callParam)
    {
        $this->function =$callFunction;

        $this->param =$callParam;

        return $this->sendAndReceive();
    }

    //连接swoole服务
    private function swooleConnect()
    {
        $this->client =new \swoole_client(SWOOLE_SOCK_TCP);

        if ($this->client->connect($this->host, $this->servser, 5)) {

            return $this->client;

        }else{

            throw new \Exception('服务连接失败：'.$this->client->errCode,909);
        }
    }

    //发送数据接收返回的数据
    private function sendAndReceive()
    {
        $send =[
            'services'=>$this->services,
            'function'=>$this->function,
            'params'=>$this->param
        ];
        //发送请求 序列化后的数据
        $this->client->send(serialize($send));

        //接收数据 序列化后的数据
        $receive = $this->client->recv();

        $this->swooleReceive($receive);

        return $this->content;
    }

    //将数据中的异常抛出
    private function swooleReceive($receive)
    {
        $data = unserialize($receive);

        if (is_array($data) && isset($data['SwooleMsg'])) {

            throw new \Exception($data['SwooleMsg'],$data['SwooleCode']);
        }

        $this->content =$data;
    }

    //服务结束 关闭swoole连接
    public function __destruct()
    {
        $this->client->close();
    }
}