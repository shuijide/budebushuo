<?php
/**
 * Created by PhpStorm.
 * User: YHC
 * Date: 2018/11/7
 * Time: 11:42
 */

namespace Library\Database;

class Work{

    protected $link =NULL;

    public function __construct($host =DB_HOST,$user =DB_USER,$pass =DB_PASS,$dbname =DB_NAME)
    {
        return $this ->connect($host,$user,$pass,$dbname);
    }

    // 数据库连接，默认选择一个数据库
    protected function connect($host,$user,$pass,$dbname)
    {
        $this ->link =new \mysqli($host,$user,$pass,$dbname);

        if ($this ->link->connect_error) throw new \Exception('数据连接失败',705);

        $this ->link ->set_charset('utf8');

        return $this->link;
    }


}