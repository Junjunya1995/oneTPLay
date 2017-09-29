<?php
use \think\facade\{
    Request
};
//配置文件
return [
    'orginal_table_prefix' => 'tp5_', //默认表前缀
    //内容进行字符替换
    'view_replace'=>[
        '__JS__'=> Request::root().'/'.Request::module().'/js',
        '__CSS__'=> Request::root().'/'.Request::module().'/css',
        '__IMG__'=> Request::root().'/'.Request::module().'/images',
    ],
];