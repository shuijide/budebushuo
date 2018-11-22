<?php
/**
 * Created by PhpStorm.
 * User: 闫好闯
 * Date: 2018/10/30
 * Time: 9:49
 */

namespace Library\Services;

class Services{

    public static function __callStatic($action,$params)
    {
        return '静态方法 '.$action.' 不存在';
    }

    public function __call($action,$params)
    {
        return '方法 '.$action.' 不存在';
    }

    //抛出异常
    public static function thowExc($string,$code =102)
    {
        throw new \Exception($string,$code);
    }
}