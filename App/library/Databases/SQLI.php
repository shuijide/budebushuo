<?php
/**
 * Created by PhpStorm.
 * User: 闫好闯
 * Date: 2018/7/3
 * Time: 15:51
 */

class Databases_SQLI{

    protected $host;
    protected $dbname;
    protected $user;
    protected $pass;
    protected $table;
    protected $sql =null;
    protected $db;
    protected $link =null;

    protected $sql_where ='';
    protected $sql_order ='';
    protected $sql_limit ='';
    protected $sql_field ='*';
    protected $sql_maybe ='';
    protected $sql_group ='';
    protected $sql_set ='';
    protected $sql_columns ='';
    protected $sql_values ='';
    protected $sql_result ='';

    public function __construct($table ='',$host =DB_HOST,$user =DB_USER,$pass =DB_PASS,$dbname =DB_NAME)
    {
        $this ->dbname =$dbname;
        $this->table =$table;
        $this ->host =$host;
        $this ->user =$user;
        $this ->pass =$pass;
        $this->checkTableName($this->table); //表名称不能为空

        return $this ->connect();
    }

    // 数据库连接，默认选择一个数据库
    protected function connect()
    {
        $this ->link =new mysqli($this->host,$this->user,$this->pass,$this->dbname);

        if ($this ->link->connect_error) throw new Exception('数据连接失败',705);

        $this ->link ->set_charset('utf8');

        return $this->link;
    }

    //关闭数据库连接
    public function closeDb()
    {
        $this->link ->close();
    }

    // 数据库选择
    public function selectDb($dbname)
    {
        return $this->link ->select_db($dbname);
    }

    //获取数据表中的全部字段 return array | false;
    public function tableColumns($table,$field ='name')
    {
        $fields =$this->fetchFields($table);

        $data =[];

        foreach ($fields as $key => $value) {

            $data[] =$value->$field;
        }

        return $data;
    }

    //查询数据表结构 return array | false;
    public function fetchFields($table)
    {
        if ($result = $this->mysqliQuery("select * from `$table` limit 1")) {

            $data =$result->fetch_fields();

            return $data;

        }else{
            throw new Exception('查询数据表：'.$table.'的表结构失败',701);
        }
    }

    //查询数据表的字段个数
    public function tableColumnCount($table)
    {
        $this->sql ="select * from `$table` limit 1";

        $result =$this->mysqliQuery();

        return $result ->field_count;
    }

    //对外输出当前的查询的SQL语句
    public function lastQuery()
    {
        return $this->sql;
    }

    //普通查询 SQL语句查询 update delete操作
    public function query($sql)
    {
        $this->sql =$sql;

        return $this->mysqliQuery();
    }

    //SQL语句查询 一条
    public function select($sql)
    {
        $this->sql =$sql;

        $result =$this->mysqliQuery();

        $data = $result ->fetch_assoc();

        $result ->free();

        $this->generalRestting();

        return $data;
    }

    //SQL语句查询 全部
    public function selectAll($sql)
    {
        $this->sql =$sql;

        $result =$this->mysqliQuery();

        $data = $result ->fetch_all(MYSQLI_ASSOC);

        $result ->free();

        $this->generalRestting();

        return $data;
    }

    /*###################################################################################################*/
    /*###################################################################################################*/
    /*################################             连贯操作             ##################################*/
    /*###################################################################################################*/
    /*###################################################################################################*/

    public function where(array $condition)
    {
        if (empty($condition)) return $this;

        $this->sql_where ='where '.$this->generalSqlCondition($condition);

        return $this;
    }

    //SQL的or条件
    public function maybe(array ...$orCondition)
    {
        if (empty($orCondition)) return $this;

        $this->sql_maybe =$this->generalSqlOrCondition($orCondition);

        return $this;
    }

    //SQL查询结果显示的字段
    public function field($field ='*')
    {
        if (empty(trim($field))) return $this;

        $this->sql_field =trim($field);

        return $this;
    }

    public function limit($limit ='')
    {
        if (empty($limit)) {
            $this->sql_limit =$limit;
        }else{
            $this->sql_limit ='limit '.trim($limit);
        }

        return $this;
    }

    public function order($order ='')
    {
        if (empty($order)) {
            $this->sql_order =$order;
        }else{
            $this->sql_order ='order by '.trim($order);
        }

        return $this;
    }

    public function group($group = '')
    {
        if (empty($group)) {
            $this->sql_group =$group;
        }else{
            $this->sql_group ='group by '.$group;
        }

        return $this;
    }

    //普通查询 获取全部信息
    public function findAll()
    {
        $this->generalSqlSupport('select',2);

        $result = $this->mysqliQuery();

        $data = $result ->fetch_all(MYSQLI_ASSOC);

        $result ->free();

        return $data;
    }

    //普通查询 获取一条信息
    public function find()
    {
        $this->generalSqlSupport('select',1);

        $result = $this->mysqliQuery();

        $data = $result ->fetch_assoc();

        $result ->free();

        return $data;
    }

    //普通查询 总记录数
    public function count()
    {
        $sql ='select '.$this->sql_field.' from `'.$this->table.'`';

        if (!empty($this->sql_where)) $sql .= ' '.$this->sql_where;

        if (!empty($this->sql_maybe)) $sql .= ' '.$this->sql_maybe;

        if (!empty($this->sql_limit)) $sql .= ' '.$this->sql_limit;

        $this->sql =$sql;

        $this->generalRestting();

        $result = $this->mysqliQuery();

        $data = $result ->num_rows;

        $result ->free();

        return $data;
    }

    //普通操作 更新
    public function update()
    {
        if (empty($this->sql_set)) throw new Exception('请使用set()方法设置需要更新的字段',617);

        $sql ='update `'.$this->table.'`'.$this->sql_set;

        if (!empty($this->sql_where)) $sql .= ' '.$this->sql_where;

        if (!empty($this->sql_maybe)) $sql .= ' '.$this->sql_maybe;

        if (!empty($this->sql_limit)) $sql .= ' '.$this->sql_limit;

        $this->sql =$sql;

        $this->generalRestting();

        $this->mysqliQuery();

        return $this->link ->affected_rows;
    }

    //普通操作 数据写入
    public function insert()
    {
        if (empty($this->sql_columns) || empty($this->sql_values)) throw new Exception('请使用values()方法设置需要写入的字段及数据',618);

        $sql ='insert into `'.$this->table.'` ( '.$this->sql_columns.' )  values ( '.$this->sql_values.' )';

        $this->sql =$sql;

        $this->mysqliQuery();

        return $this->link ->insert_id;
    }

    //普通操作 写入的数据处理
    public function values(array $values)
    {
        $columns ='';
        $worth ='';

        foreach ($values as $key =>$val) :
            $columns .='`'.$key.'`,';
            $worth .="'".$val."',";
        endforeach;

        $this->sql_columns =rtrim($columns,',');
        $this->sql_values =rtrim($worth,',');

        return $this;
    }

    //普通操作 删除
    public function delete()
    {
        $this->generalSqlSupport('delete',2);

        $this->mysqliQuery();

        return $this->link ->affected_rows;
    }

    //更新功能 set
    public function set(array $set)
    {
        $columns_set =' set';
        foreach ($set as $key => $value) {
            $columns_set .= " `" . $key . "` = '" . $value."',";
        }

        $this->sql_set =rtrim($columns_set,',');

        return $this;
    }

    /*###################################################################################################*/
    /*###################################################################################################*/
    /*################################             事务                ##################################*/
    /*###################################################################################################*/
    /*###################################################################################################*/

    //开始事务关闭自动提交
    public function workBegin()
    {
        if ($this->link ->autocommit(false)) {
            return true;
        }else{
            throw new Exception('事务启用失败',708);
        }
    }

    //开启自动提交 关闭事务
    public function workClose()
    {
        $this->link ->autocommit(true);
    }

    //提交事务
    public function workCommit()
    {
        if ($this->link ->commit()) {
            return true;
        }else{

            $this->link ->autocommit(true);

            throw new Exception('数据处理失败',707);
        }
    }

    //事务回滚
    public function workRollback()
    {
        $this->link ->rollback();
    }

    /*###################################################################################################*/
    /*###################################################################################################*/
    /*################################            预处理               ##################################*/
    /*###################################################################################################*/
    /*###################################################################################################*/

    /**
     * SQL预处理功能 查询一条记录
     * @param array $condition and查询条件
     * @param string $columns 查询结果返回的字段
     * @param array ...$orCondition or查询条件
     * @return bool|mixed
     */
    public function fetch(array $condition, $columns = '*', array ...$orCondition)
    {
        $data = $this->prepareFetch($condition, $columns , $orCondition);

        if (empty($data)) return [];

        return $data['0'];
    }

    /**
     * SQL预处理功能 查询全部记录
     * @param array $condition and查询条件
     * @param string $columns 查询结果返回的字段
     * @param array ...$orCondition or查询条件
     * @return bool|mixed
     */
    public function fetchAll(array $condition, $columns = '*', array ...$orCondition)
    {
        return $this->prepareFetch($condition, $columns , $orCondition);
    }

    /**
     * 预处理 SQL预处理功能 count查询
     * @param array $condition and查询条件
     * @param array $limit
     * @param string $columns 查询结果返回的字段
     * @param array ...$orCondition or查询条件
     * @return array|bool
     */
    public function fetchCount(array $condition, array ...$orCondition)
    {
        $data = $this->prepareFetch($condition, 'count(*)' , $orCondition);

        if (empty($data)) {
            return 0;
        }else{
            return current($data['0']);
        }
    }

    /**
     * 预处理 数据表写入一条数据 可以指定插入的ID
     * @param $insert
     * @return isertID | bool
     * @throws Exception
     */
    public function write(array $insert)
    {
        if (empty($insert)) throw new Exception('写入数据表的值不能为空',613);

        $insert_type_verify =$this->columnType(key($insert));

        $insert_keys =array_keys($insert);

        if ($insert_type_verify =='s') {
            //关联数组 其中的每一个条件都应该为关联数组
            $this->prepareInsertColumnsType($insert_keys,'s','数据类型应该全部为关联数组');

            //SQL绑定的值
            $bind_value =[];
            //SQL语句中需要绑定值的字段名
            $sql_fields ='';
            //站位符
            $hold_sign ='';
            //绑定值的字段类型
            $fields_type ='';
            foreach ($insert as $key => $value) :
                $fields_type .=$this->columnType($value);
                $hold_sign .=' ?,';
                $sql_fields .=$key.',';
                $bind_value[] =&$insert[$key];
            endforeach;
            $sql_fields =rtrim($sql_fields,',');
            $hold_sign =rtrim($hold_sign,',');
            $fields_and_bind_value =array_merge([$fields_type],$bind_value);

            $this->sql ='insert into `'.$this->table.'` ( '.$sql_fields.' ) values ('.$hold_sign.' )';

        }else if ($insert_type_verify =='i') {
            //数字数组 其中的每一个条件都应该为数字索引数组
            $this->prepareInsertColumnsType($insert_keys,'i','数据类型应该全部为数字索引数组');

            //数据表的列数
            $table_columns_count = $this->tableColumnCount($this->table);

            $insert_count =count($insert);

            if ($insert_count > $table_columns_count) throw new Exception('参数数量大于数据表的字段数量',609);

            if ($insert_count + 1 < $table_columns_count) throw new Exception('参数数量小于数据表的字段数量',610);

            if ($insert_count ==$table_columns_count) :
                //指定自增ID
                $bind_value =[];
                $hold_sign ='';
                $fields_type ='';
            else :
                //不指定自增ID
                $space =' ';
                $bind_value =[0=>&$space];
                $hold_sign =' ?,';
                $fields_type ='i';
            endif;

            foreach ($insert as $k => $val) :
                $bind_value[] =&$insert[$k];
                $fields_type .=$this->columnType($val);
                $hold_sign .=' ?,';
            endforeach;

            $hold_sign =rtrim($hold_sign,',');

            $fields_and_bind_value =array_merge([$fields_type],$bind_value);

            $this->sql ='insert into `'.$this->table.'` values ('.$hold_sign.')';

        }else{
            throw new Exception('不支持的数据类型',607);
        }

        //SQL预处理
        $stmt =$this->link ->prepare($this->sql);

        if ($stmt ==false) throw new Exception('预处理失败:'.$this->sql,704);

        call_user_func_array([$stmt,'bind_param'],$fields_and_bind_value);

        if ($stmt->execute() ==false) throw new Exception('执行预处理失败',706);

        //返回插入的ID
        return $stmt->insert_id;
    }

    /**
     * 预处理 数据表写入多条数据 可以指定插入的ID
     * @param $option insert
     * @return array|bool
     * @throws Exception
     */
    public function writeAll(array $insert)
    {
        if (empty($insert)) throw new Exception('写入数据表的值不能为空',613);

        //使用事务 routine
        $this->workBegin();

        try{
            //用于保存返回值 ID
            $insert_id =[];

            foreach ($insert as $value) :

                if (!is_array($value)) throw new Exception('$insert应该是二维数组',614);

                //写入一条记录
                $id =$this->writeRow($value);

                if ($id ==false) throw new Exception('数据写入失败',611);

                $insert_id[] =$id;

            endforeach;

            //提交事务
            $this->workCommit();

            //停止事务
            $this->workClose();

            return $insert_id;

        }catch (Exception $e){

            $this->workRollback();

            throw new Exception($e->getMessage(),$e->getCode());
        }
    }

    /**
     * 预处理 更新数据 不支持group by
     * @param array $data
     * @param array $condition
     * @param array ...$orCondition
     * @return mixed 受影响的行数
     * @throws Exception
     */
    public function modify(array $data, array $condition = [], array ...$orCondition)
    {
        if (empty($data)) throw new Exception('没有需要更新的数据',604);

        $this->prepareInsertColumnsType(array_keys($data),'s','数据类型应该全部为关联数组');

        //根据$condition条件组合SQL的where条件，组装bind_param参数
        $prepare_data =$this->prepareSqlCondition($condition);

        //根据$orCondition条件组合SQL的where条件，组装bind_param参数
        $prepare_or_data =$this->prepareSqlOrCondition($orCondition);

        //组装sql语句使用
        $sql_field =$prepare_data['field'].$prepare_or_data['field'];

        if (strlen($sql_field) ==4) :
            $sql_where ='';
        else :
            $sql_where =' where '.$sql_field;
        endif;

        //where条件中用到的bind_param需要的value
        $bind_condition_values =$this->prepareSqlParam($prepare_data['column'],$prepare_or_data['column']);

        $filed_type ='';
        $sql_set ='set';
        foreach ($data as $key => $value) :

            $filed_type .=$this->columnType($value);

            $sql_set .=' '.$key.' = ? ,';

        endforeach;

        $sql_set =rtrim($sql_set,',');

        if (empty($bind_condition_values)):
            $bind_filed_type =$filed_type;
            $bind_data =array_merge([$bind_filed_type],$data);
        else :
            $bind_filed_type =$filed_type.$bind_condition_values['0'];
            array_shift($bind_condition_values);
            $bind_data =array_merge([$bind_filed_type],$data,$bind_condition_values);
        endif;

        $bind_param_value =[];
        foreach ($bind_data as $k => $val) $bind_param_value[] =&$bind_data[$k];

        //拼接预处理使用的SQL语句
        $sql ='update `'.$this->table.'` '.$sql_set.$sql_where;

        //SQL查询条件order by
        if (!empty($this->sql_order)) $sql .= ' '.$this->sql_order;

        //SQL查询条件limit
        if (!empty($this->sql_limit)) $sql .= ' '.$this->sql_limit;

        $this->sql =$sql;

        //重置变量
        $this->prepareRestting();

        $stmt =$this->link->prepare($this->sql);

        if ($stmt ==false) throw new Exception('预处理失败:'.$this->sql,704);

        //填充预处理SQL
        call_user_func_array([$stmt, 'bind_param'], $bind_param_value);

        //预处理执行
        if ($stmt->execute() ==false) throw new Exception('执行预处理失败',706);

        //返回受影响的行数
        return $stmt->affected_rows;
    }

    /**
     * 预处理 删除数据 必须带条件 不支持group by
     * @param array $condition
     * @param array ...$orCondition
     * @return mixed
     * @throws Exception
     */
    public function remove(array $condition, array ...$orCondition)
    {
        if (empty($condition)) throw new Exception('condition条件不能为空',615);

        //根据$condition条件组合SQL的where条件，组装bind_param参数
        $prepare_data =$this->prepareSqlCondition($condition);

        //根据$orCondition条件组合SQL的where条件，组装bind_param参数
        $prepare_or_data =$this->prepareSqlOrCondition($orCondition);

        if (empty($prepare_data['column']) && empty($prepare_or_data['column'])) {
            throw new Exception('查询缺少必要的条件',603);
        }

        //组装sql语句使用
        $sql_field =$prepare_data['field'].$prepare_or_data['field'];

        //组装bind_param需要的value
        $bind_values =$this->prepareSqlParam($prepare_data['column'],$prepare_or_data['column']);

        //预处理SQL语句
        $sql ="delete from `$this->table` where ".$sql_field;

        //SQL查询条件order by
        if (!empty($this->sql_order)) $sql .= ' '.$this->sql_order;

        //SQL查询条件limit
        if (!empty($this->sql_limit)) $sql .= ' '.$this->sql_limit;

        $this->sql =$sql;

        //重置变量
        $this->prepareRestting();

        //预处理绑定的参数做引用
        $bind_param_value =[];
        foreach ($bind_values as $bind_k => $bind_val) $bind_param_value[] =&$bind_values[$bind_k];

        //执行预处理
        $stmt =$this->link ->prepare($this->sql);

        if ($stmt ==false) throw new Exception('预处理失败:'.$this->sql,704);

        //填充预处理sql
        call_user_func_array(array($stmt, 'bind_param'), $bind_param_value);

        //预处理执行
        if ($stmt->execute() ==false) throw new Exception('执行预处理失败',706);

        return $stmt->affected_rows;
    }

    /**
     * 析构函数 数据库操作写入日志 仅能写入当前进程的最后一条SQL
     * 非最后一次操作进程的SQL日志 需使用 sqlLogInsert()方法
     */
//    public function __destruct()
//    {
//        $this->sqlLogInsert();
//    }

    //写入一条SQL记录
    public function sqlLogInsert()
    {
        $this->query("insert into mysql_log (user_id,operate,user_ip) values ('{$_SESSION['user_id']}','{$this->sql}','{$_SERVER['REMOTE_ADDR']}')");
    }

    /*++++++++++++++++++++++++++++++++++++   预处理 据库查询方法的辅助方法   ++++++++++++++++++++++++++++++++++++++++++++++++*/

    //一条查询
    protected function mysqliQuery($sql ='')
    {
        $sql_str = (empty($sql)) ? $this->sql : $sql;

        if ($query =mysqli_query($this->link,$sql_str)) {
            return $query;
        }else{
            throw new Exception('SQL语句出错:'.$this->sql,702);
        }
    }

    //数据表名称检测
    protected function checkTableName($table)
    {
        if (empty($table)) throw new Exception('数据表名称不能为空',612);

        if (is_numeric($table) || is_numeric($table['0'])) throw new Exception('数据表名称格式错误',612);

        return true;
    }

    //处理预处理写入时字段类型的判断
    protected function prepareInsertColumnsType($insert_keys,$type,$str)
    {
        foreach ($insert_keys as $value) :
            if ($this->columnType($value) !=$type) throw new Exception($str,608);
        endforeach;
    }

    /**
     * 不支持any,all,group,having查询
     * @param $condition    and查询条件
     * @param string $columns   查询结果返回的字段
     * @param array ...$orCondition   or查询条件
     *              传递多个$orCondition数组则连接多个or，$orCondition内部则使用and条件连接
     *              $condition 与 $orcondition 不能同时为空,此时应该使用普通查询方法
     * @return array|bool
     */
    protected function prepareFetch($condition, $columns, $orCondition)
    {
        //两种查询条件不能同时为空
        if (empty($condition) && empty($orCondition)) throw new Exception('缺少查询条件',605);

        //根据$condition条件组合SQL的where条件，组装bind_param参数
        $prepare_data =$this->prepareSqlCondition($condition);

        //根据$orCondition条件组合SQL的where条件，组装bind_param参数
        $prepare_or_data =$this->prepareSqlOrCondition($orCondition);

        if (empty($prepare_data['column']) && empty($prepare_or_data['column'])) {
            throw new Exception('查询缺少必要的条件',603);
        }

        //组装sql语句使用
        $sql_field =$prepare_data['field'].$prepare_or_data['field'];

        //组装bind_param需要的value
        $bind_values =$this->prepareSqlParam($prepare_data['column'],$prepare_or_data['column']);

        //预处理SQL语句 下方三个if条件语句需使用连贯操作
        $sql ="SELECT $columns from `$this->table` where ".$sql_field;

        //SQL查询条件group
        if (!empty($this->sql_group)) $sql .= ' '.$this->sql_group;

        //SQL查询条件order by
        if (!empty($this->sql_order)) $sql .= ' '.$this->sql_order;

        //SQL查询条件limit
        if (!empty($this->sql_limit)) $sql .= ' '.$this->sql_limit;

        //变量赋值 方便外部查看当前SQL语句
        $this->sql =$sql;

        //重置变量
        $this->prepareRestting();

        //预处理绑定的参数做引用
        $bind_param_value =[];
        foreach ($bind_values as $bind_k => $bind_val) $bind_param_value[] =&$bind_values[$bind_k];

        //执行预处理
        $stmt =$this->link ->prepare($this->sql);

        if ($stmt ==false) throw new Exception('数据预处理失败:',704);

        //填充预处理sql
        call_user_func_array(array($stmt, 'bind_param'), $bind_param_value);

        //预处理执行
        if ($stmt->execute() ==false) throw new Exception('执行预处理失败',706);

        //结果集从mysql取出做临时存储
        if ($stmt->store_result() ==false) throw new Exception('预处理数据临时存储失败',703);

        //对取出数据的字段做判定 如果是 * 则查询数据库获取当前表的字段 用于要输出的字段的key
        if ($columns =='*') {
            $out_fields = $out_field_key =$this->tableColumns($this->table);
        }else{
            //字段可能有别名
            $out_fields =$out_field_key =$this->fieldAlias($columns);
        }

        //需要从结果集中取出的字段 做字段绑定
        $out_put=[];
        foreach ($out_fields as $out_key =>$out_val) $out_put[] =&$out_fields[$out_key];

        //要输出的字段总数
        $fields_count =count($out_put);

        //对显示的字段 绑定
        call_user_func_array(array($stmt, 'bind_result'), $out_put);

        $result =array();
        //取出结果
        while ($res =$stmt->fetch()) :

            $data =array();

            for ($i=0; $i < $fields_count ; $i++) $data[$out_field_key[$i]] =$out_put[$i];

            $result[] =$data;

        endwhile;

        $stmt ->close();

        return $result;
    }

    //预处理的SQL查询字段中使用字段别名的处理
    protected function fieldAlias($columns)
    {
        $columns =trim($columns);

        $result =[];

        if (strpos($columns,',')) {
            $fields =explode(',',$columns);

            foreach ($fields as $key => $value) :

                $field_value =trim($value);

                //字段有别名的情况(有 as 或 没有均可)
                if (strpos($columns,' ')) $field_value =$this->fieldSpace($field_value);

                $result[] =$field_value;

            endforeach;

        }elseif (strpos($columns,' ')){
            //仅有一个字段且字段有别名
            $result =[$this->fieldSpace($columns)];
        }else{

            //仅有一个字段并且字段没有做别名
            $result =[$columns];
        }

        return $result;
    }

    //处理字段别名时 处理其中的空格
    protected function fieldSpace($column)
    {
        $fields =explode(' ',$column);

        return trim(end($fields));
    }
    
    //为预处理的拼接SQL做支持
    protected function prepareSqlCondition($condition)
    {
        //查询条件拼接
        $fields ='';
        //查询条件的字段类型
        $column_type ='';
        //查询字段绑定
        $fetch_field =[];
        foreach ($condition as $con_key => $con_val) :

            if (is_array($con_val)) :
                //实体符号转换
                $convert_data = $this->prepareSignConvert($con_val);

                //返回数组 字段类型，fields后的字符串
                $column_type .= $convert_data['type'];
                $fields      .= $con_key . ' ' . $convert_data['str'] . ' and ';
                $fetch_field = $convert_data['field'];

            else:
                //检测字段类型
                $col_type =$this->columnType($con_val);
                $column_type   .= $col_type;
                $fields        .= $con_key . ' = ? and ';
                $fetch_field[] = $con_val;
            endif;
        endforeach;

        $fields ='( '.rtrim($fields,'and ').' )';

        //字段类型 和查询字段需要绑定的值
        $filed_type_and_columns =array_merge([$column_type],$fetch_field);

        return ['field'=>$fields,'column'=>$filed_type_and_columns];
    }

    //预处理中or条件整合
    protected function prepareSqlOrCondition($orCondition)
    {
        $or_field =''; //SQL语句及占位符
        $or_type =''; //预处理中的绑定值类型
        $column_value =[]; //预处理需要绑定的值
        foreach ($orCondition as $value) :

            $prepare_or_data =$this->prepareSqlCondition($value);

            $or_type .=$prepare_or_data['column']['0'];
            //移除$or_type
            array_shift($prepare_or_data['column']);
            //
            $column_value =array_merge($column_value,$prepare_or_data['column']);

            $or_field .= ' or '.$prepare_or_data['field'];
        endforeach;

        return ['field'=>$or_field,'column'=>array_merge([$or_type],$column_value)];
    }

    //预处理中bind_param需求的值
    protected function prepareSqlParam($prepare_data, $prepare_or_data)
    {

        if (!empty($prepare_data) && !empty($prepare_or_data)) {

            $bind_values_type =$prepare_data['0'].$prepare_or_data['0'];
            array_shift($prepare_data);
            array_shift($prepare_or_data);
            $bind_values =array_merge([$bind_values_type],$prepare_data,$prepare_or_data);

        }else if (!empty($prepare_data)){

            $bind_values =$prepare_data;

        }else if (!empty($prepare_or_data)){

            $bind_values =$prepare_or_data;
        }

        return $bind_values;
    }

    /*
     * 预处理 实体符号转换拼接查询语句
     * 大于号:gt,   大于等于:gte,   小于号:lt,     小于等于:lte,   不等于:ne,
     * between:bt,     not between:nbt,     in:in,      not in:nin
     */
    protected function prepareSignConvert(array $condition)
    {
        switch (key($condition)) {
            case 'gt':
                $field =current($condition);
                $field_arr =[$field];
                $type =$this->columnType($field);
                $result = '> ?';
                break;

            case 'gte':
                $field =current($condition);
                $field_arr =[$field];
                $type =$this->columnType($field);
                $result = '>= ?';
                break;

            case 'lt':
                $field =current($condition);
                $field_arr =[$field];
                $type =$this->columnType($field);
                $result = '< ?';
                break;

            case 'lte':
                $field =current($condition);
                $field_arr =[$field];
                $type =$this->columnType($field);
                $result = '<= ?';
                break;

            case 'ne':
                $field =current($condition);
                $field_arr =[$field];
                $type =$this->columnType($field);
                $result = '!= ?';
                break;

            case 'bt':
                $field_first =current($condition['bt']);
                $field_other =next($condition['bt']);
                $field_arr =[$field_first,$field_other];
                $type =$this->columnType($field_first).$this->columnType($field_other);
                $result ='between ? and ?';
                break;

            case 'nbt':
                $field_first =current($condition['nbt']);
                $field_other =next($condition['nbt']);
                $field_arr =[$field_first,$field_other];
                $type =$this->columnType($field_first).$this->columnType($field_other);
                $result ='not between ? and ?';
                break;

            case 'in':
                $type ='';
                $field_arr =[];
                $result ='in ( ';
                foreach ($condition['in'] as $key => $value) :
                    $field_arr[] =$value;
                    $type .=$this->columnType($value);
                    $result .= '?,';
                endforeach;
                $result =rtrim($result,',').' )';
                break;

            case 'nin':
                $type ='';
                $field_arr =[];
                $result ='not in ( ';
                foreach ($condition['nin'] as $key => $value) :
                    $field_arr[] =$value;
                    $type .=$this->columnType($value);
                   $result .= '?,';
                endforeach;
                $result =rtrim($result,',').' )';
                break;
            case 'like':
                $field =current($condition);
                $field_arr =["{$field}%"];
                $type =$this->columnType($field);
                $result = "like ?";
                break;

            default:
                throw new Exception('不支持'.key($condition).'查询条件',602);
                break;
        }

        return ['str'=>$result,'type'=>$type,'field'=>$field_arr];
    }


    /*++++++++++++++++++++++++++++++++++++   普通 数据库查询方法的辅助方法   ++++++++++++++++++++++++++++++++++++++++++++++++*/

    //查询SQL组装
    /**
     * @param $type 区分select和delete
     * @param int $limit_type   一条记录1，多条2
     * @throws Exception
     */
    protected function generalSqlSupport($type,$limit_type)
    {
        switch ($type) :
            case 'select':
                $sql ='select '.$this->sql_field.' from `'.$this->table.'`';
                break;
            case 'delete':
                $sql ='delete from `'.$this->table.'`';
                break;
            default:
                throw new Exception('不支持'.$type.'操作',708);
                break;
        endswitch;

        //where条件
        if (!empty($this->sql_where)) $sql .= ' '.$this->sql_where;
        //or条件
        if (!empty($this->sql_maybe)) $sql .= ' '.$this->sql_maybe;
        //group条件
        if (!empty($this->sql_group)) $sql .= ' '.$this->sql_group;
        //order条件
        if (!empty($this->sql_order)) $sql .= ' '.$this->sql_order;
        //limit条件
        if ($limit_type ==1) :
            $sql .=' limit 1';
        else :
            if (!empty($this->sql_limit)) $sql .= ' '.$this->sql_limit;
        endif;

        $this->sql =$sql;

        //重置SQL条件变量
        $this->generalRestting();
    }

    // 变量重置 防止同一个new下的数据冲突
    protected function generalRestting()
    {
        $this->sql_field ='*';

        $this->sql_where =null;

        $this->sql_maybe =null;

        $this->sql_set =null;

        $this->sql_columns =null;

        $this->sql_values =null;

        $this->sql_result =null;

        $this->prepareRestting();
    }

    //重置变量
    protected function prepareRestting()
    {
        $this->sql_group =null;

        $this->sql_order =null;

        $this->sql_limit =null;
    }

    //普通查询condition拼接
    protected function generalSqlCondition($condition)
    {
        $condition_str ='( ';

        foreach ($condition as $key => $value) :
            //in between等条件
            if (is_array($value)) :
                //实体符号转换
                $condition_str .=$key.' '.$this->generalSignConvert($value).' and ';
            else :
                $condition_str .=$key." = '{$value}' and ";
            endif;
        endforeach;

        return rtrim($condition_str,'and ').' )';
    }

    //普通查询orCondition拼接
    protected function generalSqlOrCondition(array $orCondition)
    {
        $or_where =' ';
        foreach ($orCondition as $value) :
            $or_where .='or '.$this->generalSqlCondition($value).' ';
        endforeach;

        return $or_where;
    }

    //普通查询实体符号转换
    protected function generalSignConvert(array $condition)
    {
        switch (key($condition)) {
            case 'gt':
                $result = '> '.current($condition);
                break;

            case 'gte':
                $result = '>= '.current($condition);
                break;

            case 'lt':
                $result = '< '.current($condition);
                break;

            case 'lte':
                $result = '<= '.current($condition);
                break;

            case 'ne':
                $result = '!= '.current($condition);
                break;

            case 'bt':
                $result ="between '".current($condition['bt'])."' and '".end($condition['bt'])."'";
                break;

            case 'nbt':
                $result ="not between '".current($condition['nbt'])."' and '".end($condition['nbt'])."'";
                break;

            case 'in':
                $in_str ='in ( ';

                foreach ($condition['in'] as $key => $value) :
                    $in_str .="'{$value}' , ";
                endforeach;

                $result =trim($in_str,', ').' )';
                break;

            case 'nin':
                $in_str ='not in ( ';

                foreach ($condition['nin'] as $key => $value) :
                    $in_str .="'{$value}' , ";
                endforeach;

                $result =trim($in_str,', ').' )';
                break;
            case 'like':
                $result ="like '".current($condition)."%'";
                break;

            default:
                throw new Exception('不支持'.key($condition).'查询条件',602);
                break;
        }

        return $result;
    }

    // 判断字段类型
    protected function columnType($column)
    {
        $type =gettype($column);

        switch ($type) {
            case 'integer':
               $result ='i';
        		break;
            case 'boolean':
                $result ='b';
                break;
            case 'double':
                $result ='d';
                break;
            case 'string':
                $result ='s';
                break;
            default:
                throw new Exception('不支持的字段类型',606);
                break;
        }
        return $result;
    }
}