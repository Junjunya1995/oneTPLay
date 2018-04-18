<?php
/**
 * Description of UserInfo.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-07-27 14:46
 */

namespace app\internal;

use think\facade\{
    App, Hook, Session,Cache,Config
};


/**
 * Description of Condition
 * 用户信息类
 * @author static7
 */
class UserInfo
{
    /**
     * 自动登录用户
     * @param array $data 用户信息数组
     * @return bool
     */
    public function autoLogin(array $data)
    {
        /* 记录登录SESSION和COOKIES */
        $auth = [
            'uid' => $data['uid'],
            'username' => $data['nickname'],
            'last_login_time' => $data['last_login_time']
        ];
        //缓存用户信息
        Cache::set('user_info', $data);
        Session::set('user_auth', $auth);
        Session::set('user_auth_sign', data_auth_sign((array)$auth));
        //记录行为
        $param = [
            'action' => 'user_login',
            'model' => __CLASS__,
            'record_id' => $data['uid'],
            'user_id'=>$data['uid']
        ];
        Hook::listen('user_behavior', $param);
        return true;
    }

    /**
     * 获取用户信息
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function info()
    {
        $user_id=$this->userId();
        if (empty($user_id)){
            return false;
        }
        $data=Cache::get('user_info');
        if ($data) {
            return $data;
        }
        $data = App::model('Member')->oneUser(['uid', $this->userId()]);
        $data && Cache::set('user_info', $data);
        return $data;
    }

    /**
     * 检测用户是否登录
     * @return integer 0-未登录，大于0-当前登录用户ID
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function userId()
    {
        $user = Session::get('user_auth');
        if (empty($user)) {
            return 0;
        }
        return Session::get('user_auth_sign') == data_auth_sign((array)$user) ? (int)$user['uid'] : 0;

    }

    /**
     * 注销当前用户
     * @return void
     */
    public function logout()
    {
        Session::delete(['user_auth', 'user_auth_sign']);
        Cache::rm('user_info');
        return ;
    }

    /**
     * 检测当前用户是否为管理员
     * @param int $uid 用户id
     * @return bool true-管理员，false-非管理员
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function isAdmin(int $uid = 0) {
        $user_id = (int)$uid ?: $this->userId();
        return (int)$user_id && ((int)$user_id === (int)Config::get('config.user_administrator'));
    }

    /**
     * 根据用户ID获取用户昵称
     * @param  integer $uid 用户ID
     * @author staitc7 <static7@qq.com>
     * @return string       用户昵称
     */
    public function nickname(int $uid = null) {
        if (empty($uid)){
            return Session::get('user_auth.username');
        }
        //获取缓存数据
        $list = Cache::get('sys_user_nickname_list');
        if (isset($list[$uid])) { // 查找用户信息
            return $list[$uid];
        }
        if (empty($list)) {
            $info = App::model('Member')->oneUser(['uid', $uid],'nickname,uid'); //获取用户信息
            if (empty($info)) {
                return null;
            }
            Cache::set('sys_user_nickname_list', [$info['uid']=>$info['nickname']]);
            if (empty($list)){
                return $info['nickname'];
            }
            $list[$info['uid']] = $info['nickname'];
        }

        //缓存用户
        $count = count($list);
        $max = Config::get('key.user_max_cache') ?: 500;
        while ($count--> $max) {
            array_shift($list);
        }
        return $info['nickname'] ?? '';
    }
}