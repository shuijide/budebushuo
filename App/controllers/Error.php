<?php

use Yaf\Controller_Abstract;

class ErrorController extends Controller_Abstract{

    public function errorAction($exception)
    {
        $content =[
            'code'=>$exception->getCode(),
            'message'=>$exception->getMessage()
        ];

        $this->display('error',$content);
    }
}