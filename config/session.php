<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------
use think\facade\{
    Env
};

return [
    'id'             => Env::get('session.id', ''),
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => Env::get('session.var_session_id',''),
    // SESSION 前缀
    'prefix'         => Env::get('session.prefix',''),
    // 驱动方式 支持redis memcache memcached
    'type'           => Env::get('session.type',''),
    // 是否自动开启 SESSION
    'auto_start'     => true,
    //sessionkey前缀
    'session_name'=>Env::get('session.name',''),
    // redis主机
    'host' => Env::get('redis.host','127.0.0.1'),
    // redis端口
    'port' => Env::get('redis.port',6379),
    // 密码
    'password' => Env::get('redis.password',''),
];
