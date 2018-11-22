<?php
namespace Sroute;

/*
 * 格式，控制器名称(user),请求方法(GET、GET 需大写格式),方法名(list、add、modify)
 */

class route{

    public $route =[];

    public function __construct()
    {
        $this->route =[
            'index'=>[
                'GET'=>[
                    'test',
                    'index',
                    'articlecate'
                ],
                'POST'=>[
                    'login','logout'
                ]
            ],
            'manage'=>[
                'GET'=>[
                    'articlecateadd','articlecatelist','articledetail'
                ],
                'POST'=>[
                    'articleedit'
                ]
            ]
        ];
    }

    //重定向到首页
    public function locationIndex()
    {
        header('location:http://www.budebushuo.com/index.html');
    }

    //重定向404
    public function location404()
    {
        header('location:http://www.budebushuo.com/index.html');
    }
}