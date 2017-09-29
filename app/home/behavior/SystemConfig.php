<?php

namespace app\home\behavior;

use think\facade\{
    Config, Cache, App, Log
};

/**
 * Description of SystemConfig
 * 系统配置初始化
 * @author static7
 */
class SystemConfig
{
    /**
     * 系统配置读取并缓存
     * @author staitc7 <static7@qq.com>
     */

    public function run()
    {
        $config = Cache::get('home_config');
        if (empty($config)) {
            $config = App::model('Deploy', 'models')->lists(['area', 'in', '0,1']);
            Cache::tag('system_config')->set('home_config', $config);
            Log::record("[ 系统配置 ]：初始化成功");
        }
        Config::set($config, 'home_config');
    }

}
