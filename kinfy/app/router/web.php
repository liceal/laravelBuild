<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 14:57
 */

use Kinfy\Http\Router;



Router::GET('/user/liceal',function (){
    echo "this is liceal web!!!";
});

Router::GET('/',function (){
    echo '网站首页';
});

Router::GET('/article','ArticleController@index');

Router::GET('/11/{id}/{name?}',function ($id,$name=0){
    echo "this is double 11 web".' '.$id.' '.$name;
});

Router::GET('/11/{id}/{name}/{value}',function ($id,$name,$value){
    echo "this is double 11 web".' '.$id.' '.$name.' '.$value;
});

Router::MATCH(['GET','POST'],'article/{id}/{name}','ArticleController@index');



