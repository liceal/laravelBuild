<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 14:59
 */

namespace Kinfy\Http;

class Router
{
    //$_SERVER
    //REQUEST_METHOD 请求类型
    //REQUEST_URI 访问地址

    //当路由未匹配的时候执行的回调函数，默认为空
    public static $notFound = null;

    //拆分规则
    public static $delimiter = '@';

    //控制器位置
    public static $namespace = '';

    //存放当前注册的所有路由规则
    public static $routes=[];

    //魔术方法 当访问的方法不存在时自动使用
    public static function __callStatic($name, $arguments)
    {
        //name 不存在的请求名字 字符串，arguments 参数 数组
        // TODO: Implement __callStatic() method.
        //自定义添加路由规则
        $name = strtoupper($name); //路由请求转大写
        if (count($arguments) >= 2) { //判断路由请求合理

            //判断是多种请求规则路由
            if ($name == 'MATCH' && count($arguments)>=3 && is_array($arguments[0])){
                $partten = self::filterPath($arguments[1]); //路由路径
                $callback = $arguments[2]; //方法
                //将多种请求全部注册路由
                foreach ($arguments[0] as $request_type) {
                    $request_type = strtoupper($request_type);
                    self::$routes[$request_type][$partten] = $callback;
                }
            } else { //单种请求路由注册
                $partten = self::filterPath($arguments[0]); //路由路径
                $callback = $arguments[1]; //方法
                self::$routes[$name][$partten] = $callback; //注册路由规则
            }
        }
    }

    //判断是否带参数路径
    private static function withArgs($path){
        $pathPart = explode('/',$path);
        $argSub = []; //参数在第几个/的后面
        $args = []; //控制路由里的所有的参数变量
        $staticArgs = []; //路由内/分割后静态的地址块
        $flag = false; //是否有变量
        $len = 0;//实际长度
        $lens = 0;//默认参数的个数
        $arglen = 0;//参数个数
        foreach ($pathPart as $key=>$part) {
            if (strlen($part) >= 2 && $part[0] == '{' && $part[strlen($part)-1] == '}') { //判断是参数
//                array_push($argSub,$key = $part); //在第几个/后面
                if ($part[strlen($part)-2] == '?'){//判断是可不填参数
                    array_push($argSub,$key = '{Args?}');//标记
                    $lens++;
                }else{
                    array_push($argSub,$key = '{Args}'); //标记
                }
                $flag = true;
                $arglen++;
//                array_push($args,substr($part,1,strlen($part)-2)); //参数名
            } else { //不是参数
                array_push($argSub,$key = $part); //插入键值队
            }
        }

        $arr = [];
        $arr[0] = $argSub;//变量位置
        $arr[1] = $flag; //是否有参数
        $arr[2] = count($pathPart); //实际长度
        $arr[3] = $len-$lens; //也可用长度
        $arr[4] = $lens; //默认参数个数
        $arr[5] = $arglen; //参数个数
        $arr[6] = $arglen - $lens; //至少参数个数
//        print_r($arr[2]);exit();
        return $arr; //返回所有位置的类型
    }

    //带参地址判断
    private static function IfWithArgs($request_type,$partten){
        foreach (self::$routes as $key => $route) { //遍历所有注册路由 键：请求类型 值：地址=>方法
//            print_r($route);
            if ($request_type == $key) { //判断请求类型一致
                foreach ($route as $path=>$fn){ //遍历此路由 键：地址 值：方法
//                    print_r($path);exit();
                    $flag = true; //是否继续遍历
                    $pathArgs = self::withArgs($path); //判断路由所有地址 返回数组[0]：/分割位置 [1]：bool是带参路由
                    if($pathArgs[1]){//判断是否带参路由
                        $part =explode('/',$partten);//分割当前请求地址
                        $Args = [];//存放所有变量参数
                        foreach ($part as $key => $value){//遍历请求地址
                            if (isset($part[$key]) && isset($pathArgs[0][$key])){
                                if ( $part[$key] != $pathArgs[0][$key] && $pathArgs[0][$key]=='{Args?}'||$pathArgs[0][$key]=='{Args}'){//判断是否是参数
                                    array_push($Args,$part[$key]);//保存参数
                                }
                            } else {
                                $flag = false;
                            }
                        }

                        if((count($Args)==$pathArgs[5] || count($Args)==$pathArgs[6]) && $flag){ //参数个数大于0
//                            print_r($fn);die();
                            if (is_callable($fn)) {//判断是个可执行的方法 判断web.php
                                $fn(...$Args); //调用
                            } else {
                                list($class,$method) = explode('@',$fn);//拆分字符串得到@左右两边的字符 给$class $method
                                $class = self::$namespace.$class; //实例化此位置的php
                                $obj = new $class();
                                $obj->{$method}(...$Args); //调用 指定实例化 的 指定方法
                            }
                            die();
                        }
                    }
                }
            }
        }
    }

    //路径参数 '/' 处理
    private static function filterPath($path){
        return '/'.trim($path,'/');
    }

    public static function dispatch()
    {
        //当前访问的类型
        $request_type = strtoupper($_SERVER['REQUEST_METHOD']);
        //当前访问的域名
        $partten = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';//没填默认访问/

        if (isset(self::$routes[$request_type][$partten])) //有这个域名
        {
            $callback = self::$routes[$request_type][$partten];
            if (is_callable($callback)) {//判断是个可执行的方法 判断web.php
                call_user_func($callback); //调用
            } else {
                list($class,$method) = explode('@',$callback);//拆分字符串得到@左右两边的字符 给$class $method
                $class = self::$namespace.$class; //实例化此位置的php
                $obj = new $class();
                $obj->{$method}(); //调用 指定实例化 的 指定方法
            }
        } else { //否则进入错误页面

            self::IfWithArgs($request_type,$partten);//进入错误页面前，执行带参路径的实现

            if (is_callable(self::$notFound)) { //如果可执行
                call_user_func(self::$notFound); //执行
            } else {
                header("HTTP/1.1 404 Found");//发出404警告
                exit;
            }
        }

    }

}
