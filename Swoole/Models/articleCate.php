<?php
/**
 * Created by PhpStorm.
 * User: YHC
 * Date: 2018/11/12
 * Time: 11:06
 */
namespace Models;

use Library\Database\Model;

class articleCate extends Model{
    //获取一条信息
    public static function fetchOne($conditon,$columns ='*',$orConditon =[])
    {
        $db =parent::DB('sj_article_cate');

        $result =$db->fetch($conditon,$columns,$orConditon);

        return $result;
    }

    //获取全部信息
    public static function fetchAll($condition =[],$columns ='*',$order ='id asc',$orConditon =[])
    {
        $db =parent::DB('sj_article_cate');

        $db->order($order);

        $result =$db->fetchAll($condition,$columns,$orConditon);

        return $result;
    }

    //写入表
    public static function insertOne($data)
    {
        $db =parent::DB('sj_article_cate');

        $result =$db->write($data);

        return $result;
    }

    //更新表
    public static function updateUser($data,$condition,$orCondition =[])
    {
        $db =parent::DB('sj_article_cate');

        $result =$db->modify($data,$condition,$orCondition);

        return $result;
    }
}