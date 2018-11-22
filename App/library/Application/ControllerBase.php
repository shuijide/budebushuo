<?php
namespace Application;
use Mount\swooleClient;
use Yaf\Controller_Abstract;
use formVerifyPlugin;
use Yaf\View_Simple;

class ControllerBase extends Controller_Abstract{

    protected $params; //前端get、post传递的值
    private $messageCode =SUCCESS_CODE;
    private $messageInfo ='';

    public function init()
    {
        $method =$this->getRequest()->getMethod();

        switch ($method) :
            case 'GET':
                $query =&$_GET;
                break;
            case 'POST':
                $query =&$_POST;
                break;
        endswitch;

        $this->params =formVerifyPlugin::trimValue($query);
    }

    /**
     * 信息输出
     * @param $content
     */
    public function sendContent($content ='')
    {
        header('Content-Type:application/json; charset=utf-8');

        $msg =[
            'code'=>$this->messageCode,
            'message'=>$this->messageInfo,
            'data'=>$content
        ];

        $data = json_encode($msg);

        exit($data);
    }

    /**
     * 设置输出码
     * @param $code
     */
    public function setCode($code)
    {
        if (!is_int($code)) {
            throw new \Exception('code应为整型');
        }

        $this->messageCode =$code;
    }

    public function setMessage($message)
    {
        if (!is_string($message)) {
            throw new \Exception('message应为字符串');
        }

        $this->messageInfo =$message;
    }

    //swoole
    public function swooleServer($serverName,$servicesName)
    {
        $swooleClient =new swooleClient($serverName,$servicesName);

        return $swooleClient;
    }
}