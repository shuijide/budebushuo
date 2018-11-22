<?php
/**
 * Created by PhpStorm.
 * User: 闫好闯
 * Date: 2018/10/26
 * Time: 13:48
 */

namespace Models;

use Library\Database\Model;

class user extends Model {

    //获取一条信息
    public static function fetchOne($condition,$columns ='*',$orCondition =[])
    {
        $db =parent::DB('sj_user');

        $result =$db->fetch($condition,$columns,$orCondition);

        return $result;
    }

    //更新表
    public static function updateUser($data,$condition,$orCondition =[])
    {
        $db =parent::DB('sj_user');

        $result =$db->modify($data,$condition,$orCondition);

        return $result;
    }

    //登录成功 更新登录表 写入登录日志
    public static function loginSuccessSupport($userData,$userCon,$logData)
    {
        //事务
        $work =parent::TA();
        $work->workBegin();
        try{

            $userRes =self::updateUser($userData,$userCon);

            $logRes =userLoginLog::insertOneUserLog($logData);

            if ($userRes ==false || $logRes ==false) {
                throw new \Exception('');
            }

            $work->workCommit();

            return true;

        }catch (\Exception $e){

            $work->workRollback();

            return false;
        }
    }

    //验证密码
    public static function passwordVerify($password,$hash)
    {
        return password_verify($password,$hash);
    }

    //生成用户码
    public static function enUserCode($userId,$groups)
    {
        $len =strlen($userId);

        return 'BDBS-'.($userId - $len).'-'.$len.'-'.($groups + $len);
    }

    //从用户码中获取用户ID
    public static function deUserCode($code)
    {
        if (empty($code) || strpos($code,'-') === FALSE) {
            return false;
        }

        $codeArr =explode('-',$code);

        //前缀
        if ($codeArr['0'] != 'BDBS') {
            return false;
        }
        //ID
        if (!is_int($codeArr['1'] + 0) || $codeArr['1'] < '0') {
            return false;
        }
        //长度
        if (!is_int($codeArr['2'] + 0) || $codeArr['2'] < '1') {
            return false;
        }
        //用户组
        if (!is_int($codeArr['3'] + 0) || $codeArr['3'] < '1') {
            return false;
        }

        return [
            'code'=>$codeArr['0'],
            'id'=>$codeArr['1'] + $codeArr['2'],
            'length'=>$codeArr['2'],
            'groups'=>$codeArr['3'] - $codeArr['2']
        ];
    }
}