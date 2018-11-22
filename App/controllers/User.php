<?php

use Application\ControllerBase;

class UserController extends ControllerBase {

    //添加用户 所有字段必填
    public function addAction()
    {
        //表单提交的必须的字段
        $must =[
            'login_name','realname','phone','groups','password','people','firstday','idcard','edu','sex',
            'home_province','home_county','new_province','new_city','new_county','new_address','home_phone'
        ];
        //表单提交的需为int类型的字段
        $int_fields =[
            $this->params['people'],$this->params['groups'],$this->params['edu'],$this->params['home_province'],$this->params['sex'],
            $this->params['home_county'],$this->params['new_province'],$this->params['new_city'],$this->params['new_county']
        ];
        //字段验证
        $this->bsUserMix($this->params,$must,$int_fields);

        if (formPlugin::checkPassword($this->params['password']) ==false) $this->sendContent('密码长度小于6位，请重新输入',404);

        $user_md =new userModel();

        $result =$user_md->bsUserAdd($this->params);

        if ($result ===true) {
            $this->sendContent('添加成功');
        }else{
            $this->sendContent($result,404);
        }
    }

    //用户信息修改 不含密码 登录名 用户状态
    public function modifyAction()
    {
        $must =[
            'orders','realname','phone','groups','people','firstday','idcard','edu','sex',
            'home_province','home_county','new_province','new_city','new_county','new_address','home_phone'
        ];

        $int_fields =[
            $this->params['people'],$this->params['groups'],$this->params['edu'],$this->params['home_province'],$this->params['sex'],
            $this->params['home_county'],$this->params['new_province'],$this->params['new_city'],$this->params['new_county']
        ];

        $this->bsUserMix($this->params,$must,$int_fields);

        if (formPlugin::positiveInt([$this->params['orders']],false) ==false) $this->sendContent('信息异常，请刷新页面之后重新填写',404);

        $id =$this->params['orders'];
        unset($this->params['orders']);

        $user_md  =new userModel();

        $res =$user_md->bsUserUpdate($this->params,$id);

        if ($res ==0){
            $this->sendContent('用户信息为改变');
        }elseif ($res ==false){
            $this->sendContent('修改失败，请稍后再试',404);
        }else{
            $this->sendContent('修改成功');
        }
    }

    /**
     * 用户列表
     * page必填，其他选填,page最小为1，
     * 返回值中的total为分页数量
     */
    public function listAction()
    {
        $request =$this->params;

        if (formPlugin::positiveInt([$request['page']],false) ==false) $this->sendContent('访问错误',404);

        //搜索项
        $condition =[];
        //登录名
        if (!empty($request['login_name'])) $condition['name'] =['like'=>$request['login_name']];
        //真实名称
        if (!empty($request['realname'])) $condition['username'] =['like'=>$request['realname']];
        //性别
        if (!empty($request['sex'])) :
            if (in_array($request['sex'],[1,2,3]) ==false) $this->sendContent('访问错误',404);
            $condition['sex'] =$request['sex'];
        endif;
        //生日
        if (!empty($request['firstday'])):
            if (formPlugin::dateFormat($request['firstday']) ==false) $this->sendContent('访问错误',404);
            $condition['birthday'] =$request['firstday'];
        endif;

        //页码转换为limit需要的条件
        $page =formPlugin::page($request['page']);

        $user_md =new userModel();

        $count =$user_md->bsPageCount($condition);

        if ($count ==0) {
            $data =[];
        }else{
            $data = $user_md ->bsUserSerachList($condition,$page);
        }

        $this->sendContent(['total'=>$count,'list'=>$data]);
    }

    //用户添加 用户修改使用
    private function bsUserMix($request,$must,$int_fields){

        if (formPlugin::checkEmpty($request,$must) ==false) $this->sendContent('请输入完整信息后再提交保存',404);

        if (formPlugin::checkRealname($request['realname']) ==false) $this->sendContent('姓名错误',404);

        if (formPlugin::positiveInt($int_fields,false) ==false) $this->sendContent('提交的个人信息格式错误',404);

        if (formPlugin::checkMobile($request['phone']) ==false) $this->sendContent('手机号码错误',404);

        if (formPlugin::checkMobile($request['home_phone']) ==false) $this->sendContent('家庭联系手机号错误',404);

        if (formPlugin::dateFormat($request['firstday']) ==false) $this->sendContent('生日格式错误',404);
    }

    public function swooleAction()
    {
        $params =$this->params;

        $this->sendContent($params);

//        $link =$this->swooleServer(WEB_USER_SERVICES,'userServices');
//
//        $data = $link->ceshi(777,444);
//        $data = $link->test(777);



//        echo '<pre>';
//        var_dump($data);
//
//        return false;
    }

    public function swoolAction()
    {
        $link =$this->swooleServer(WEB_ADMIN_SERVICES,'userServices');

        $data = $link->ceshi(777,666);

        echo '<pre>';
        var_dump($data);

        return false;
    }

    /**
     * 基础类中的错误：
     *      参数类型错误 621
     *      php自身的false 708
     * 控制器中的错误：
     *      请求中的错误 803
     *
     */
}