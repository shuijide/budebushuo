<?php
/**
 * Created by PhpStorm.
 * User: YHC
 * Date: 2018/11/12
 * Time: 15:32
 */
namespace Home\IndexServices\Services;

use Library\Services\Services;
use Models\articleCate;
use Models\user;

class indexServices extends Services{

    //导航分类
    public static function navArticleCate($code,$sid ='0')
    {
        $condition =[
            'is_vaild'=>1,
            'sid'=>$sid
        ];

        $columns ='id as article_id,name as article_name';

        $cateData =articleCate::fetchAll($condition,$columns);

        //后台权限用户展示进入后台的分类链接
        if (!empty($code)) {
            $userData =user::deUserCode($code);
            if ($userData !=false && $userData['groups'] == '1') {
                array_push($cateData,['article_id'=>'/bridge/index.html','article_name'=>'进入后台']);
            }
        }

        return $cateData;
    }
}