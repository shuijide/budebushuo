<?php
/**
 * Created by PhpStorm.
 * User: YHC
 * Date: 2018/11/8
 * Time: 10:30
 */
use Application\ControllerAdmin;

class ManageController extends ControllerAdmin {

    //添加分类
    public function articleCateAddAction()
    {
        try{
            $params =formVerifyPlugin::trimValue($this->params);

            $must =['article_name','article_second','article_sort'];

            if (!formVerifyPlugin::keyExist($params,$must) || !formVerifyPlugin::positiveInt([$params['article_second']])) {
                formVerifyPlugin::thowExc('参数错误');
            }
            if (!formVerifyPlugin::checkEmpty($params,['article_name'])) {
                formVerifyPlugin::thowExc('分类名称不能为空');
            }
            if (!formVerifyPlugin::positiveInt([$params['article_sort']]) || $params['article_sort'] > 999) {
                formVerifyPlugin::thowExc('排序格式错误');
            }

            //使用swoole服务
            $link =$this->swooleServer(WEB_Admin_SERVICES,'adminServices');
            $data = $link->articleCateNameAdd($params['article_name'],$params['article_sort'],$params['article_second']);

            $this->sendContent($data);

        }catch (\Exception $e){
            $this->setCode(ERROR_CODE);
            $this->setMessage($e->getMessage());
            $this->sendContent();
        }
    }

    //分类列表
    public function articleCateListAction()
    {
        try{

            //使用swoole服务
            $link =$this->swooleServer(WEB_Admin_SERVICES,'adminServices');
            $data = $link->articleCateList();

            $this->sendContent($data);

        }catch (\Exception $e){
            $this->setCode(ERROR_CODE);
            $this->setMessage($e->getMessage());
            $this->sendContent();
        }
    }

    //添加文章 编辑文章
    public function articleEditAction()
    {
        try{
            $params =formVerifyPlugin::trimValue($this->params);

            $must =['title','desc','text','serial','cate'];

            if (!formVerifyPlugin::keyExist($params,$must)
                || !formVerifyPlugin::positiveInt([$params['serial']])
                || !formVerifyPlugin::positiveInt([$params['cate']],false)) {
                formVerifyPlugin::thowExc('参数错误');
            }
            if (empty($params['title'])) {
                formVerifyPlugin::thowExc('标题不能为空');
            }
            if (empty($params['desc'])) {
                formVerifyPlugin::thowExc('描述不能为空');
            }
            if (empty($params['text'])) {
                formVerifyPlugin::thowExc('正文不能为空');
            }

            $data =[
                'article_title'=>$params['title'],
                'article_desc'=>$params['desc'],
                'article_text'=>$params['text'],
                'article_cate'=>$params['cate']
            ];
            //使用swoole服务
            $link =$this->swooleServer(WEB_Admin_SERVICES,'adminServices');

            if ($params['serial']) {
                //更新
                $data =$link->oneArticleUpdate($data,['id'=>$params['serial']]);
            }else{
                //写入
                $data = $link->allArticleInsert($data);
            }

            $this->sendContent($data);

        }catch (\Exception $e){
            $this->setCode(ERROR_CODE);
            $this->setMessage($e->getMessage());
            $this->sendContent();
        }
    }

    //获取一条文章信息
    public function articleDetailAction()
    {
        try{
            $params =formVerifyPlugin::trimValue($this->params);

            if (!formVerifyPlugin::keyExist($params,['serial']) || !formVerifyPlugin::positiveInt([$params['serial']],false)) {
                formVerifyPlugin::thowExc('参数错误');
            }

            //使用swoole服务
            $link =$this->swooleServer(WEB_Admin_SERVICES,'adminServices');
            $data = $link->oneArticleById($params['serial']);
            $this->sendContent($data);

        }catch (\Exception $e){
            $this->setCode(ERROR_CODE);
            $this->setMessage($e->getMessage());
            $this->sendContent();
        }
    }



    //权限
    public function powerAction()
    {

    }

    //添加用户可操作的action
    public function actionAddAction()
    {

    }
}