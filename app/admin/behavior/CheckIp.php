<?php

namespace app\admin\behavior;

use think\facade\{
    Log,Config,Request
};
use app\facade\UserInfo;
use traits\controller\Jump;

/**
 * Description of CheckIp
 * 检测用户访问的ip
 * @author static7
 */
class CheckIp {

    use Jump;
    /**
     * 检测用户IP
     */

    public function run() {
        $allow_ip = Config::get('config.admin_allow_ip');
        $ip = Request::ip();
        if (!UserInfo::isAdmin() && $allow_ip) {
            !in_array($ip, explode(',', $allow_ip)) && $this->error('禁止访问');// 检查IP地址访问
        }
        Log::record("[ 访问者IP ]：" . $ip,'Auth');
    }

}
