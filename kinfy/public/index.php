<?php

//调用映射根目录，就可以使用根目录制定文件夹的名字app...
use \Kinfy\Http\Router;

require_once __DIR__.'./../vendor/autoload.php';

require_once __DIR__.'./../app/router/web.php';//将里面的方法放这里

//重定义错误页面
Router::$notFound = function (){
    require_once __DIR__.'./../app/resource/404.html';
    die();
};

//重定义控制器位置
Router::$namespace = '\\App\\Controller\\';

Router::dispatch();//默认调用此方法



