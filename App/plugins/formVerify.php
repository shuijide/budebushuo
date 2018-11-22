<?php

use Yaf\Plugin_Abstract;

class formVerifyPlugin extends Plugin_Abstract {

    // trim 去除变量两侧的空
    public static function trimValue(array $data){
        $res =array();
        foreach ($data as $key => $value) {
            $res[$key] =trim($value);
        }
        return $res;
    }

    /**
     * @param array $input
     * @param array $must
     * @return bool
     */
    public static function keyExist(array $input,array $must){
        $inputKay =array_keys($input);

        if (empty($inputKay)) return false;

        foreach ($must as $key => $mustValue) {
            if (!in_array($mustValue,$inputKay)) return false;
        }
        return true;
    }

    //验证值是否为空
    public static function checkEmpty(array $input ,array $must){

        foreach ($must as $key => $value) {
            if ($input[$value] =='0') continue;
            if (empty($input[$value])) return false;
        }
        return true;
    }

    // 手机号验证
    public static function checkMobile($mobile){
        if (preg_match('/^1\d{10}$/',$mobile)) {
            return true;
        }else{
            return false;
        }
    }

    //中文敏感词验证
    public static function publicWord($word){
        if (!empty($word) ) {
            //验证敏感词

            return true;
        }else{
            return false;
        }
    }

    //正则验证中文
    public static function chineseWord($word){
        if (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $word)) {
            return true;
        } else {
            return false;
        }
    }

    //真实名称验证 名称长度 2-6
    public static function checkRealname($word){

        if (mb_strlen($word,'UTF-8') >= 2 && mb_strlen($word,'UTF-8') <= 6) {
            if (self::chineseWord($word)) {
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    //邮箱验证
    public static function checkEmail($email){
        if (preg_match('/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/',$email)) {
            return true;
        }else{
            return false;
        }
    }

    //校验密码格式
    public static function checkPassword($password){
        if (preg_match('/^[a-z0-9A-Z_-`~!@#\$\^&\*\(\)=|\{\}\':;\',\[\]\.<>\/\?~]{6,}$/',$password)) {
            return true;
        }else{
            return false;
        }
    }

    //生成密码
    public static function makePassword($password){

        return password_hash($password,PASSWORD_BCRYPT);
    }

    //密码验证
    public static function passwordVerify($pass,$hash){
        return password_verify($pass,$hash);
    }

    //验证时间格式
    public static function timeFormat(array $timeArr ,$timeLong =false){

        foreach ($timeArr as $key => $value) {

            $timeLen =strlen($value);

            if ($timeLong){
                if (strtotime($value) ==false || $timeLen !=19) return false; //年月日 时分秒
            }else{
                if (strtotime($value) ==false || $timeLen !=10) return false; //年月日
            }
        }
        return true;
    }

    //正整数判断 >=0
    public static function positiveInt(array $number ,$isZero =true){

        if (empty($number)) return false;

        if ($isZero) {
            $preg ='/^[0-9]+$/'; //允许为0
        }else{
            $preg ='/^([1-9]+)|([1-9][0-9])+$/'; //不允许为0
        }

        foreach ($number as $key => $value) {

            if (preg_match($preg,$value)) {
                continue;
            }else{
                return false;
            }
        }

        return true;
    }

    //清空cookie 无返回值
    public static function clearCookie(array $cookie){
        foreach ($cookie as $value) {
            if(!empty($_COOKIE[$value])){
                setcookie($value,'',time() -1,'/');
            }
        }
    }

    //登录成功 设置cookie
    public static function setCookie($key, $value)
    {
        setcookie($key, $value, LOGIN_EXPIRE, '/');
    }

    //时间戳验证 需大于 2018-01-01 00:00:00 1514764800
    public static function timeStamp(array $time){

        foreach ($time as $value) {
            if(!preg_match ("/^\d{10}$/", $value)){
                return false;
            }
            if ($value < 1514764800) return false;
        }
        return true;
    }

    //验证URL格式
    public static function verifyUrl($url){

        if(preg_match ("/^(http?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/", $url)){
            return true;
        }else{
            return false;
        }
    }

    //data格式验证
    public static function dateFormat($date){

        if (empty($date)) return false;

        if (date('Y-m-d',strtotime($date)) !=$date) return false;

        return true;
    }

    //昵称长度
    public static function nickNameLen($nickname)
    {
        if (mb_strlen($nickname,'UTF-8') > 14) return false;

        return true;
    }

    //昵称格式验证
    public static function nickNameFormat($nickname)
    {
        if (preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u",$nickname)){
            return true;
        } else{
            return false;
        }
    }

    //生成一个4位的随机数
    public static function makeRandom()
    {
        return mt_rand(1111,9999);
    }

    //字符不多于250个
    public static function varcharLimit($str)
    {
        if (mb_strlen($str,'UTF-8') <= 250) {
            return true;
        }else{
            return false;
        }
    }

    //银行卡号简单正则验证 16位 或 19位
    public static function bankCardNumberVerify($cardNumber)
    {
        $pre = preg_match('/^([1-9]{1})(\d{13,18})$/', $cardNumber);

        if (empty($pre)) return false;

        return true;
    }

    /**
     * text文本长度
     * @param $text
     * @param bool $empty 是否允许为空
     * @return bool
     */
    public static function textLimit($text,$empty =false)
    {
        if ($empty) {
            //允许空
            if (empty($text)) return true;
        }else{
            //不允许空
            if (empty($text)) return false;
        }
        if (strlen($text) >=65535) return false;

        return true;
    }

    //验证固定电话
    public static function checkTelephone($number)
    {
        if (empty($number)) return false;

        if (preg_match('/^\d{3,4}-\d{8}$/',$number)) {
            return true;
        }else{
            return false;
        }
    }

    //验证固定电话 手机号
    public static function checkMobileAndTelephone($number)
    {
        $mobile =self::checkMobile($number);

        $phone =self::checkTelephone($number);

        if ($mobile ==false && $phone ==false) return false;

        return true;
    }

    //简单验证json
    public static function isSimpleJson($json)
    {
        $data = json_decode($json,true);

        if (empty($data)) return false;

        if (!is_array($data)) return false;

        return true;
    }

    //抛出异常
    public static function thowExc($data,$code =ERROR_CODE)
    {
        throw new \Exception($data,$code);
    }

    /**特殊符号验证
     * @param $string
     * @param bool $type 默认不允许特殊符号
     * @return bool
     */
    public static function specialSymbol($string)
    {
        if (!is_string($string)) {
            return false;
        }

        $regEn="/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\/|\;|\\' | \`|\-|\=|\\\|\|/isu";
        $regCn ='/[·！#￥（——）：；“”‘、，|《。》？、【】[\]]/isu';

        if (preg_match($regEn,$string) || preg_match($regCn,$string)) {
            return true;
        }

        return false;
    }

    //账号长度
    public static function accountLimit($account)
    {
        if (empty($account) || !is_string($account)) {
            return false;
        }

        if (mb_strlen($account,'UTF-8') > 25) {
            return false;
        }

        return true;
    }
}