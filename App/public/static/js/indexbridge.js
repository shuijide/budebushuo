
var indexBHTML =angular.module('indexBHtml',[]);

indexBHTML.config(['$locationProvider', function ($locationProvider) {
    $locationProvider.html5Mode(true);
}]);
//导航
indexBHTML.controller("navController",['$scope','$http','$templateCache',
    function ($scope,$http,$templateCache) {

        //导航栏切换
        $scope.isCateHidden =false;
        $scope.isRoadHidden =true;
        $scope.navToggle =function () {
            $scope.isCateHidden =true;
            $scope.isRoadHidden =false;
        }

        //分类导航鼠标事件
        $scope.navCateStyle =-1;
        $scope.navCateOver =function (num) {

            $scope.navCateStyle =num;
        }
        $scope.navCateLeave =function () {
            $scope.navCateStyle =-1;
        }

        //分类信息存储
        $scope.articleCate =[];

        $scope.nowTime =(new Date()).getTime();
        //存储分类的过期时间
        $scope.expireTimeStamp =$scope.nowTime + navTimeExpire;

        if (isEmpty(localStorage.getItem('navCateTimeB'))) {
            $scope.navCateTimeStamp =0;
        }else{
            $scope.navCateTimeStamp =localStorage.getItem('navCateTimeB');
        }

        $scope.init =function (){
            //设置了两分钟的本地存储分类有效期
            if ($scope.navCateTimeStamp > $scope.nowTime && !isEmpty(localStorage.getItem('navCateB'))) {

                $scope.articleCate =JSON.parse(localStorage.getItem('navCateB'));
            }else{
                $http({
                    method : 'GET',
                    url : '/index/articlecate',
                    cache:$templateCache
                }).then(function (response) {

                    if (response.data.code !=101) {
                        messageTopCenter(response.data.message);
                    }else{
                        let info =response.data.data;

                        if (!isEmpty(info)) {
                            //记录写入存储的时间
                            localStorage.setItem('navCateTimeB',$scope.expireTimeStamp);
                            //存储导航信息
                            localStorage.setItem('navCateB',JSON.stringify(info));
                            $scope.articleCate =info;
                        }
                    }
                },function () {
                    messageTopCenter('服务异常');
                });
            }
        };
    }
]);

//编辑添加文章
indexBHTML.controller("artCateEditCtrl",['$scope','$http','$templateCache','$httpParamSerializerJQLike','$location',
    function ($scope,$http,$templateCache,$httpParamSerializerJQLike,$location) {

        $scope.serial =($location.search().serial) ? $location.search().serial : 0;
        $scope.cateId =0;
        $scope.artCate =($location.search().cate) ? $location.search().cate : 0;
        $scope.artTitle ='';
        $scope.artDesc ='';
        $scope.artText ='';
        $scope.cateList ={};
        $scope.articleDetail ={
            serial:$scope.serial,
            cateId:$scope.cateId,
            artCate:$scope.artCate,
            artDesc:$scope.artDesc,
            artText:$scope.artText
        };

        //文章分类
        $scope.articleCateList =function (){

            $http({
                method : "GET",
                url : "/manage/articleCateList",
                cache : $templateCache
            }).then(function (response) {

                $scope.cateList =angularRespons(response);

                for (let key in $scope.cateList){
                    if ($scope.cateList[key].id ==$scope.artCate) {
                        $scope.cateSelect =$scope.cateList[key];
                    }
                }
            });

            //获取当前的文章
            if ($scope.serial != 0) {
                $scope.getArticle();
            }
        };

        //获取文章
        $scope.getArticle =function getArticle (){

            if ($scope.serial == 0) return;

            $http({
                method : 'GET',
                url : '/manage/articleDetail',
                params : {serial:$scope.serial},
                cache : $templateCache
            }).then(function (response) {

                let res=angularRespons(response);

                if (!isEmpty(res)) {
                    $scope.articleDetail =res;
                }
            })
        };

        //select change 事件
        $scope.cateChange =function (cateId){
            echo(cateId)
        }

        //提交保存
        $scope.artCateSubmit =function () {

            if (isEmpty($scope.articleDetail)) {
                return;
            }
            if (isEmpty($scope.articleDetail.artTitle)) {
                messageTopCenter('标题不能为空');
                return;
            }
            if (isEmpty($scope.articleDetail.artDesc)) {
                messageTopCenter('描述不能为空');
                return;
            }
            if (isEmpty($scope.articleDetail.artText)) {
                messageTopCenter('正文不能为空');
                return;
            }

            $http({
                method : 'POST',
                url : '/manage/articleEdit',
                data:$httpParamSerializerJQLike($scope.articleDetail),
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                cache:$templateCache

            }).then(function (response) {

                let res =angularRespons(response);
                echo(res)

            }),function () {

            }
        }

}]);