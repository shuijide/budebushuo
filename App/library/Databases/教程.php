<?php
# mysqli数据库类的使用方法
function exampleAction()
{
    /* ######################  where()方法的$condition使用方法  ######################### */

    //[等值查询:一维数组]
    $condition =['id'=>1];

    //[不等于:ne]
    $condition =[
        'id'=>[
            'ne'=>1
        ]
    ];

    //[大于:gt],[大于等于:gte],[小于:lt],[小于等于:lte] 语法相同
    $condition =[
        'id'=>[
            'gt'=>1
        ]
    ];

    //[in条件:in],[not in条件:nin] 语法相同
    $condition =[
        'id'=>[
            'in'=>[
                1,2,3
            ]
        ]
    ];

    //[between:bt],[not between:nbt] 语法相同
    $condition =[
        'id'=>[
            'bt'=>[
                1,10
            ]
        ]
    ];

    //组合查询条件 多个条件在一个数字即可：如下查询为：where name ='zhangSan' and id > 1 and id !=2 id in (3,4,5)
    $condition =[
        'name' =>'zhangSan',
        'id'=>[
            'gt'=>1
        ],
        'id'=>[
            'ne'=>2
        ],
        'id'=>[
            'in'=>[
                3,4,5
            ]
        ]
    ];

    /* #######################  maybe()方法的$orCndition使用方法  ########################## */
    //$orCondition语法与$condition语法相同,支持多个$orCondition，
    //多个$orCondition之间为and连接；maybe()方法必须与where()方法共同使用
    $orConditionA =[
        'name'=>'liSi',
        'id'=>[
            'in'=>[
                1,2,3
            ]
        ]
    ];

    //or条件2
    $orConditionB =[
        'name'=>[
            'ne'=>'zhangSan'
        ]
    ];

    /* #######################  values()方法的$values使用方法  ########################## */
    //insert()方法必须与values()方法一起使用
    //如下的SQL语句为：insert into table (name,age,sex) values ('张三','18','女');
    $values =[
        'name'=>'张三',
        'age'=>'18',
        'sex'=>'女'
    ];


    $db =new SQLI('user');



    /*普通法法*/



    /**
     * 一、SQL查询方法：  [find(void):查询一条记录],[findAll(void):查询全部记录]
     *
     * 二、SQL更新方法：  [update(void):更新记录]，需配合set(array)方法使用
     *
     * 三、SQL写入方法：  [insert(void):写入一条记录],需配合values(array)方法使用
     *
     * 四、SQL删除方法:   [delete(void):删除记录]
     *
     * 五、SQL统计方法：  [count(void):记录总数]
     *
     * 六、SQL语法的条件：
     * where(array),maybe(array1,array2,...),group(string),order(string),limit(string);需配合find()或findAll()使用；
     *
     * 七、values(array $values): insert()时，$values的键为欲写入数据表的字段，$values的值为字段的值
     *
     * 八、set(array) :update()时，必须组合使用set()方法
     */

    $find1 =$db ->find();   /*查询当前数据表的一条记录 无任何条件*/

    $find2 =$db ->where($condition)->find();    /*where条件查询一条记录*/

    $find3 =$db ->where($condition)->limit('10,100')->find();  /*limit条件查询一条记录*/

    $find4 =$db ->where($condition)->group('sex')->order('id')->find(); /*各种组合条件查询一条记录 顺序随意排列即可*/

    $findAll1 =$db ->findAll();  /*无条件查询数据表的全部记录*/

    $findAll2 =$db ->where($condition)->findAll();  /*where条件查询全部记录*/

    $findAll3 =$db ->where($condition)->limit('10')->group('sex')->order('id desc')->findAll(); /*查询10条记录并做分组做排序*/

    /*如下的查询语句为:where ($condition) or (name = 'liSi' and id in (1,2,3)) or (name != 'zhangSan')*/
    $findAll4 =$db ->where($condition)->maybe($orConditionA,$orConditionB)->order('id asc')->findAll();

    $set =['name'=>'lisi','age'=>'12','sex'=>'男'];
    $update1 =$db ->set($set)->update(); /*无条件的更新*/

    $update2 =$db ->where($condition) ->limit('1,10') ->set($set) ->update();    /*有条件的更新*/

    $insert =$db ->values($values) ->insert(); /*数据写入*/

    $delete1 =$db ->delete(); //删除全部数据

    $delete2 =$db ->where($condition) ->limit(2) ->delete(); //有条件的删除操作



    /*预处理方法*/



    //预处理支持部分连贯操作方法:order(string),limit(string),group(string);用法同上;也可不使用连贯操作；
    //$condition或$orCondition两种条件需至少有一个

    $fetchRow1 =$db->fetchRow($condition,'name as myname ,age ,sex',$orConditionA,$orConditionB); //查询一条记录

    $fetchRow2 =$db->order('id desc')->limit(100,200)->group('sex')->fetchRow($condition,'*',$orConditionA);

    $fetchAll1 =$db->fetchAll($condition,'*',$orConditionB); //查询全部记录

    $fetchAll2 =$db->order('id asc')->limit(10)->fetchAll($condition);

    //$data使用方法与$values相同
    $data =['name'=>'lisi','age'=>'12','sex'=>'男'];
    $renew1 =$db->renewRow($data,$condition,$orConditionA,$orConditionB); //更新数据

    $renew1 =$db->order('id desc')->limit('12')->renewRow($data);

    $delete1 =$db->remove($condition); //数据删除 $condition为必须条件

    $delete2 =$db->order('id desc')->limit('2')->remove($condition);

    //$insert方法与$values相同
    $insert =['name'=>'lisi','age'=>'12','sex'=>'男'];
    $writeRow =$db->writeRow($insert); //写入一条数据

    $insert =[
        ['name'=>'lisi','age'=>'12','sex'=>'男'],
        ['name'=>'lisi','age'=>'12','sex'=>'男'],
        ['name'=>'lisi','age'=>'12','sex'=>'男']
    ];
    $writeAll =$db->writeAll($insert); //写入多条数据 使用了事务功能



    /*  事务  */


    $db->workBegin(); //开启事务

    $db->workCommit(); //提交事务

    $db->workRollback(); //事务回滚

    $db->workClose(); //关闭事务



    /* 原SQL语句 */

    $select =$db->select('select * from `user` where id >11'); //返回一条记录查询

    $selectAll =$db->selectAll('select * from `user` where id > 1'); //查询全部记录

    //update delete等非select操作
    $update =$db->query('update `user` set name =111 where id > 11'); //更新操作

    $delete =$db->query('delete from `user` where id =12'); //删除操作

}

?>

