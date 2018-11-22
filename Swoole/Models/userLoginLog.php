<?php
/**
 * Created by PhpStorm.
 * User: YHC
 * Date: 2018/11/7
 * Time: 11:19
 */
namespace Models;

use Library\Database\Model;

class userLoginLog extends Model{
    //更新表
    public static function updateUserLog($data,$condition,$orCondition =[])
    {
        $db =parent::DB('sj_user_login_log');

        $result =$db->modify($data,$condition,$orCondition);

        return $result;
    }

    //写入表 一条
    public static function insertOneUserLog($data)
    {
        $db =parent::DB('sj_user_login_log');

        $result = $db->write($data);

        return $result;
    }

    //写入表 多条
    public static function insertAllUserLog($data)
    {
        $db =parent::DB('sj_user_login_log');

        $result = $db->writeAll($data);

        return $result;
    }
}