<?php
/**
 * Created by PhpStorm.
 * User: 闫好闯
 * Date: 2018/10/24
 * Time: 11:21
 */

namespace Home\UserServices\Services;

use Library\Services\Services;
use Models\user;

class userServices extends Services {

    //用户登录 验证
    public static function verifyAccount($account,$passowrd,$ip)
    {
        $userData =user::fetchOne(['account'=>$account]);
        //用户不存在
        if (empty($userData)) {
            return 'USER_ERROR';
        }
        //密码验证失败
        if (user::passwordVerify($passowrd,$userData['login_pass']) ==false) {
            return 'PASS_ERROR';
        }
        //修改用户登录时间 IP 写进登录记录表
        $nowTime =date('Y-m-d H:i:s');

        $userUp =[
            'login_ip'=>$ip,
            'login_time'=>$nowTime
        ];

        if (empty($userData['login_ip'])) {
            $userUp['last_login_ip'] =$ip;
        }else{
            $userUp['last_login_ip'] =$userData['login_ip'];
        }

        if (empty($userData['login_time'])) {
            $userUp['last_login_time'] =$nowTime;
        }else{
            $userUp['last_login_time'] =$userData['login_time'];
        }

        $logInsert =[
            'user_id'=>$userData['id'],
            'method'=>1,
            'login_time'=>time(),
            'login_ip'=>ip2long($ip)
        ];

        $work =user::loginSuccessSupport($userUp,['id'=>$userData['id']],$logInsert);

        if ($work) {
            return [
                'username'=>$userData['account'],
                'ip'=>$userData['login_ip'],
                'time'=>$userData['login_time'],
                'code'=>user::enUserCode($userData['id'],$userData['groups'])
            ];
        }else{
            return 'SYSTEM_ERROR';
        }
    }

}