<?php
use \think\facade\{
    Request
};
//配置文件
return [
    //内容进行字符替换
    'view_replace'=>[
        '__JS__'=> Request::root().'/'.Request::module().'/js',
        '__CSS__'=> Request::root().'/'.Request::module().'/css',
        '__IMG__'=> Request::root().'/'.Request::module().'/images',
    ],
    //语言列表
    'lang_list'=>['zh-cn','zh-tw','ja-jp','en-us'],
];