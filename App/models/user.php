<?php

class userModel extends Databases_SQLI{
    //$mapping格式：[字段名称=>映射名称]
    protected $mapping = [
        'id'        => 'orders',
        'name'      => 'login_name',
        'state'     => 'type',
        'sex'       => 'sex',
        'username'  => 'realname',
        'mobile'    => 'phone',
        'usergroup' => 'groups',
        'userpass'  => 'password',
        'nation'    => 'people',
        'birthday'  => 'firstday',
        'id_number' => 'idcard',
        'education' => 'edu',
        'old_province'     => 'home_province',
        'old_county'       => 'home_county',
        'current_province' => 'new_province',
        'current_city'     => 'new_city',
        'current_county'   => 'new_county',
        'current_address'  => 'new_address',
        'home_mobile'      => 'home_phone',
        'create_time'      => 'found_time'
    ];

    public function __construct()
    {
        parent::__construct('bs_user');
    }

    //登录名查询一个用户 bs_user表
    public function bsOneByName($login_name)
    {
        return $this->fetchRow(['name'=>$login_name]);
    }

    //bs_user表总数
    public function bsPageCount($condition)
    {
        $count = $this->where($condition)->count();

        return ceil($count / PAGE_SIZE);
    }

    //bs_user用户列表
    public function bsUserSerachList($condition,$page ='')
    {
        if ($page ==='') :
            $limit ='';
        else :
            $limit =$page.','.PAGE_SIZE;
        endif;

        $fields ='id,state,name,username,mobile,usergroup,sex,nation,birthday,id_number,old_province,old_county,current_province,current_city,current_county,current_address,home_mobile,create_time';
        $columns =mappingPlugin::alias($fields,$this->mapping);

        return $this->limit($limit)->field($columns)->where($condition)->findAll();
    }

    //bs_user表添加用户
    public function bsUserAdd($from_data)
    {
        if (($params = mappingPlugin::formSupport($from_data,$this->mapping)) ==false) return '提交保存的信息不符合要求';

        //查看当前用户是否存在
        if (!empty($this->bsOneByName($params['name']))) return '用户登录名已存在';

        //密码加密
        $params['userpass'] =formPlugin::makePassword($params['userpass']);

        $res =$this->writeRow($params);

        if ($res) {
            return true;
        }else{
            return '添加用户失败，请稍后再试';
        }
    }

    //bs_user表更新一条数据
    public function bsUserUpdate($from_data,$userId)
    {
        //定义本次更新的字段
        $update_fields =['username','mobile','usergroup','nation','birthday','id_number','education','old_province','old_county','current_province','current_city','current_county','current_address','sex','home_mobile'];

        if (($params = mappingPlugin::formSupport($from_data,$this->mapping)) ==false) return '提交保存的信息不符合要求';

        //表单提交的字段必须为本次定义的字段
        if (!empty(array_diff($update_fields,array_keys($params)))) return '提交的信息异常，请刷新页面后重新提交';

        return $this->renewRow($params,['id'=>$userId]);
    }
}