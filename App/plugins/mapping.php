<?php
/**
 * Created by PhpStorm.
 * User: 闫好闯
 * Date: 2018/7/12
 * Time: 9:23
 */

/**
 * Class mappingPlugin
 * 数据表字段映射
 *
 * 格式:如表bs_user 对应 public static function bsUser{}
 */

class mappingPlugin extends Yaf_Plugin_Abstract{

    /**
     * @param $data 表单接收的数据 其中的key如果不存在与$mapping中，则返回false
     * @return array|bool
     */
    public static function formSupport($data,$mapping){

        if (empty($data) || empty($mapping)) return false;

        $map =array_flip($mapping);

        if ($map ==NULL) return false;

        $result =[];
        foreach ($data as $key => $value) :
            if (isset($map[$key])) :
                $result[$map[$key]] =$value;
            else :
                return false;
            endif;
        endforeach;

        if (empty($result)) return false;

        return $result;
    }

    //SQL as 查询使用
    public static function alias($fields,$mapping){

        if (empty($fields) || empty($mapping)) throw new Exception('$fields和$mapping不能为空',619);

        $columns ='';
        if (trim($fields) =='*') :

            foreach ($mapping as $map_key => $map_val) {

                $columns .= $map_key.' as '.$map_val.',';

            }

        else :
            if (!is_string($fields)) throw new Exception('$fields应为字符串类型',620);

            $fields_arr =explode(',',$fields);

            foreach ($fields_arr as $key => $value) :

                if (isset($mapping[$value]) ==false) throw new Exception($value.'字段不存在',621);

                $columns .=$value.' as '.$mapping[$value].',';
            endforeach;

        endif;

        return rtrim($columns,',');
    }
}