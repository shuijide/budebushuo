<?php
/**
 * Created by PhpStorm.
 * User: YHC
 * Date: 2018/11/12
 * Time: 9:57
 */

namespace Admin\AdminServices\Services;

use Library\Services\Services;
use Models\article;
use Models\articleCate;
use Models\nodeAction;
use Models\user;
use Models\userGroups;

class adminServices extends Services {

    //验证权限
    public static function grantVeryfy($code,$controller,$action,$userType =1)
    {
        if (empty($code)) {
            return false;
        }
        if (false == $result =user::deUserCode($code)) {
            return false;
        }

        //查询用户ID 用户组
        $userCon =[
            'type'=>$userType,
            'id'=>$result['id'],
            'state'=>1
        ];
        $userData =user::fetchOne($userCon,'id,groups');

        if (empty($userData)) {
            return false;
        }
        //分组数据
        $groupsCon =[
            'id_valid'=>1,
            'id'=>$userData['groups']
        ];
        $groupsData =userGroups::fetchOne($groupsCon,'node');

        if (empty($groupsData) || empty($groupsData['node'])) {
            return false;
        }
        //节点ID
        $nodeIdArr =explode(',',$groupsData['node']);

        //查询action对应的
        $nodeCon =[
            'is_vaild'=>1,
            'controller'=>$controller,
            'action'=>$action,
            'id'=>['in'=>$nodeIdArr]
        ];
        $nodeData =nodeAction::fetchAll($nodeCon,'controller,action');

        if (empty($nodeData)) {
            return false;
        }

        return true;
    }

    //添加分类
    public static function articleCateNameAdd($name,$sort,$sid)
    {
        //同一个分类下的分类名称不能重复
        $cateCon =[
            'name'=>$name,
            'sid'=>$sid,
            'is_vaild'=>1
        ];
        $cateData =articleCate::fetchOne($cateCon,'id');

        if (!empty($cateData)) {
            throw new \Exception('分类名称已存在');
        }

        $insertData =[
            'name'=>$name,
            'sid'=>$sid,
            'sort'=>$sort
        ];
        $cateRes =articleCate::insertOne($insertData);

        if ($cateRes != false) {
            return ['result'=>1];
        }else{
            return ['result'=>0];
        }
    }

    //获取文章分类
    public static function articleCateList()
    {
        $cateData =articleCate::fetchAll(['is_vaild'=>1],'id,name');

        return $cateData;
    }

    //添加文章
    public static function allArticleInsert($data)
    {
        //验证文章分类是否存在
        $cateData =articleCate::fetchOne(['id'=>$data['article_cate']]);

        if (empty($cateData)) {
            throw new \Exception('数据异常');
        }

        return article::insertOne($data);
    }

    //获取一个文章
    public static function oneArticleById($id)
    {
        $columns ='id as serial,article_cate as cateId,article_title as artTitle,article_desc as artDesc,article_text as artText';

        $data =article::fetchOne(['id' =>$id],$columns);

        return $data;
    }

    //更新文章
    public static function oneArticleUpdate($data,$condition)
    {
        $artData =article::fetchOne($condition);

        if (empty($artData)) {
            throw new \Exception('不存在的文章');
        }

        return article::update($data,$condition);
    }
}