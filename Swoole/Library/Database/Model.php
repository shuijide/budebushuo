<?php
/**
 * Created by PhpStorm.
 * User: YHC
 * Date: 2018/11/6
 * Time: 17:48
 */
namespace Library\Database;

class Model{

    private static $dbConnect =NULL;

    public static function __callStatic($action,$params)
    {
        return '静态方法 '.$action.' 不存在';
    }

    public function __call($action,$params)
    {
        return '方法 '.$action.' 不存在';
    }

    //初始化数据库请求
    public static function DB($table)
    {
       if (is_null(self::$dbConnect)) {

           self::$dbConnect = new Table();
       }

       self::$dbConnect->setTable($table);

       return self::$dbConnect;
    }

    //初始化事务 transaction
    public static function TA()
    {
        if (is_null(self::$dbConnect)) {

            self::$dbConnect = new Table();
        }

        return self::$dbConnect;
    }
}