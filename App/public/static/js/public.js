//全局变量
var height =$(window).height();
var width =$(window).width();
var alertMsgWidth =200; //消息弹框宽度
var alertMsgTopPos =100; // 消息弹框距离顶部高度
var loginFormHeight =135; //登录DIV高度
var currentX; //鼠标点击位置X
var currentY; //鼠标点击位置Y
var navShowWidth =760; //导航分类显示
var navTimeExpire =60000; //导航分类存储时间 毫秒

//调试 760
function echo(data) {
    console.log(data);
}


//处理angular返回的数据
function angularRespons(response) {
    if (response.data.code !=101) {
        messageTopCenter(response.data.message);
    }else{
        return response.data.data;
    }
}

//判断是否为空
function isEmpty(obj) {

    let name;

    for ( name in obj ) {
        return false;
    }
    return true;
}

//特殊符号
function specialSymbol(username) {
    let regEn = /[`~!@#$%^&*()_+<>?:"{},.\/;'[\]]/im;
    let regCn = /[·！#￥（——）：；“”‘、，|《。》？、【】[\]]/im;

    if(regEn.test(username) || regCn.test(username)) {
        return true;
    }
    return false;
}

//密码格式验证 数字字母特殊符号（英文格式）
function checkPassword(password) {
    let reg =/^[a-z0-9A-Z_-`~!@#$^&*()=|{}':;',\[\].<>/?~]{6,}$/;
    return reg.test(password);
}

//定位窗口中间
function winCenter(widBox) {
    if (width < widBox) {
        return '0px';
    }else{
        return (Math.floor((width - widBox) /2)) + 'px';
    }
}

function trim(text){
    let whitespace = "[\\x20\\t\\r\\n\\f]",
    rtrim = new RegExp( "^" + whitespace + "+|((?:^|[^\\\\])(?:\\\\.)*)" + whitespace + "+$", "g" )
    return text == null ? "" : ( text + "" ).replace( rtrim, "" );
}

//获取cookie
function getCookie() {
    let cokeObj =document.cookie;
    let item;
    let cokeVal;
    let cokeTrimKey;
    let cokeTrimValue;
    let result =[];

    if (isEmpty(cokeObj) == true) {
        return [];
    }

    let cokeArr =cokeObj.split(';');

    for(item in cokeArr) {
        cokeVal =cokeArr[item].split('=');
        cokeTrimKey =trim(cokeVal[0]);
        cokeTrimValue =trim(cokeVal[1]);
        result[cokeTrimKey] =cokeTrimValue;
    }

    if (isEmpty(arguments[0]) == true) {

        return result;

    }else{
        if (result.indexOf[arguments[0]] != -1) {

            return result[arguments[0]];
        }

        return '';
    }
}

//导航隐藏 显示
window.onresize=function(){

    if ($(window).width() < navShowWidth) {
        $(".article-cate").show();
    }else{
        $(".article-cate").hide();
    }
}

//设置登录用户信息
function setLoginData(loginName) {
    $(".login-a").text(loginName);
    $(".login-a").css({'color':'#FFFFFF'});
    $(".login-button").text(loginName);
    $(".login-toggle").hide();
}
//追加一个angular退出功能
function appendAgLogout() {

    $(".login-a").attr('ng-click',"logout()");

    $(".login-button").attr('ng-click',"logout()");
}

// 顶部中心信息弹窗
function messageTopCenter(data){
    if (data =='undefined') {
        data =" ";
    }
    //定位
    let msgBox =winCenter(alertMsgWidth);

    $(".alertMessage").css({'left':msgBox});
    $(".alertMessage").css({'top':alertMsgTopPos+'px'});

    message(data);
}

// 鼠标点击位置弹窗
function messageCurrent(data) {

    let wHalf =Math.floor(alertMsgWidth / 2);
    let posX =currentX;
    let posY =currentY;
    let wLeft =currentX - alertMsgWidth; //弹窗距离左侧边框的距离
    let wRight =width - (currentX + alertMsgWidth); //弹窗距离右侧边框的距离

    //水平位置
    if (width <= alertMsgWidth) {
        //位置小于弹窗宽度
        posX ='0px';
    }else if (wLeft >= wHalf && wRight >= wHalf) {
        //位置左侧大于等于弹窗一半 位置右侧大于等于弹窗一半
        posX =(currentX - wHalf)+'px';
    }else{
        posX =winCenter(alertMsgWidth);
    }

    //垂直位置
    let posHeight =currentY - loginFormHeight - 50;
    if (posHeight > 0) {
        posY =posHeight+'px';
    }else {
        posY ='0px';
    }

    $(".alertMessage").css({'left':posX});
    $(".alertMessage").css({'top':posY});
    message(data);
}

//提示信息弹窗
function message(data) {

    $(".alertMessage").text(data);

    $(".alertMessage").fadeIn("slow");

    $(".alertMessage").fadeOut(3000);
}

//head foot 使用

var indexAg =angular.module('indexHtml',[]);

//导航的登录名称显示
indexAg.controller("startController",['$scope',
    function ($scope) {

        //页面加载时 是否显示下层导航
        if (width > navShowWidth) {
            $scope.navShow =false;
            $scope.navHide =true;
        }else{
            $scope.navShow =true;
            $scope.navHide =false;
        }

        // 已登录用户设置登录信息
        if (isEmpty(getCookie('code')) ==false && isEmpty(localStorage.getItem('loginName')) ==false) {
            setLoginData(localStorage.getItem('loginName'));
        }else{
            setLoginData('登录');
        }
    }]);

//导航栏的分类
indexAg.controller("navcateController",['$scope','$http','$templateCache',
    function ($scope,$http,$templateCache) {

        $scope.articleCate =[];

        $scope.nowTime =(new Date()).getTime();

        //存储分类的过期时间
        $scope.expireTimeStamp =$scope.nowTime + navTimeExpire;

        if (isEmpty(localStorage.getItem('navCateTime'))) {
            $scope.navCateTimeStamp =0;
        }else{
            $scope.navCateTimeStamp =localStorage.getItem('navCateTime');
        }

        $scope.init =function (){
            //设置了两分钟的本地存储分类有效期
            if ($scope.navCateTimeStamp > $scope.nowTime && !isEmpty(localStorage.getItem('navCate'))) {

                $scope.articleCate =JSON.parse(localStorage.getItem('navCate'));
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
                            localStorage.setItem('navCateTime',$scope.expireTimeStamp);
                            //存储导航信息
                            localStorage.setItem('navCate',JSON.stringify(info));
                            $scope.articleCate =info;
                        }
                    }
                },function () {
                    messageTopCenter('服务异常');
                });
            }
        };

        //退出登录
        $scope.logout =function () {
            //清除后台登录信息
            $http({
                method : 'POST',
                url : '/index/logout',
                data : '',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                cache:$templateCache
            }).then(function (response) {

            });

            setTimeout("location.href='/index.html'",2000)
        }
    }
]);

//登录
indexAg.controller("loginController",['$scope','$http','$templateCache','$httpParamSerializerJQLike',
    function ($scope,$http,$templateCache,$httpParamSerializerJQLike) {

        $scope.loginSubmit =function (entry) {

            if (isEmpty(getCookie('code')) == false) {
                messageCurrent('您已登录');
                return;
            }
            if (entry.username == undefined || entry.password == undefined) {
                return;
            }
            if (isEmpty(entry.username)) {
                messageCurrent('请输入你的登录名');
                return;
            }
            if (isEmpty(entry.password)) {
                messageCurrent('请输入密码');
                return;
            }
            if (specialSymbol(entry.username) || entry.username > 25) {
                messageCurrent('登录名格式错误');
                return;
            }
            if (entry.password.length < 6 || checkPassword(entry.password) ==false) {
                messageCurrent('密码格式错误');
                return;
            }
            //发送请求
            $http({
                method:"POST",
                url:"/index/login",
                data:$httpParamSerializerJQLike({username:entry.username,password:entry.password}),
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                cache:$templateCache
            }).then(function(response) {

                if (response.data.code !=101) {
                    messageTopCenter(response.data.message);
                }else{
                    let info =response.data.data;
                    let loginName;

                    if (isEmpty(info.username)) {
                        loginName ='';
                    }else{
                        loginName =info.username.substr(0,1);
                    }
                    localStorage.setItem('loginName',loginName);

                    appendAgLogout();

                    setLoginData(loginName);
                }
            },function() {
                messageTopCenter('服务异常');
            });
        }
    }]);

// jQuery Start
$(function () {

    // 鼠标位置
    $(window).on("click",function (event) {
        let evt = event || window.event;
        currentX =evt.clientX;
        currentY =evt.clientY;
    });

    //显示登录层
    $(".login-button,.login-a").on("click",function (event) {

        if (isEmpty(getCookie('code')) == false) {
            let wid;
            let ske;
            let evt = event || window.event;
            let X =evt.clientX;

            if ($(window).width() > navShowWidth) {
                wid =125;
                ske =60;
            }else{
                wid =60;
                ske =30;
            }
            $(".logout-div").css({'width':wid+'px'});
            $(".logout-div").css({'margin-left':(X - ske )+'px'});
            $(".logout-div").show();
            return;
        }

        if (height > 230) {

            let marginTop;

            if (height > width && height > 460) {
                marginTop =(Math.floor($(window).height() / 2) - 200) + 'px';
            }else{
                marginTop =(Math.floor($(window).height() / 2) - 150) + 'px';
            }

            $(".login-container").css({'margin-top':marginTop});
        }else{
            $(".login-container").css({'margin-top':'10px'});
        }

        //输入框位置
        if (width > 380) {
            let marginLeft =(Math.floor((width - 380) /2)) + 'px';
            $(".login-container").css({'margin-left':marginLeft});
        }else{
            $(".login-container").css({'margin-left':'10px'});
        }

        $(".login-toggle").show();
    });

    //隐藏登录层
    $(".login-cancel").on("click",function () {
        $(".login-toggle").hide();
    });

    //隐藏退出登录
    $(".logout-cancel").on("click",function () {
        $(".logout-div").hide();
    })
});

// jQuery End!
