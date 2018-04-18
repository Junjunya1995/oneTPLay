<?php
use think\facade\{
    Config,Session,Log,Request,Cache
};

/**
 * 数据签名认证
 * @param  array $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign(array $data) {
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    return sha1($code);//生成签名
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pk
 * @param string $pid parent标记字段
 * @param string $child level标记字段
 * @param int $root 根
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = [];
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = [];
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] = &$list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent = &$refer[$parentId];
                    $parent[$child][] = &$list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 根据用户ID获取用户昵称
 * @param  integer $uid 用户ID
 * @author staitc7 <static7@qq.com>
 * @return string       用户昵称
 */
function get_nickname(int $uid = 0) {
    if ((int)$uid<1){
        return Session::get('user_auth.username');
    }
    //获取缓存数据
    $list = Cache::get('sys_user_nickname_list');
    if ($list && array_key_exists($uid,$list)) {
        return $list[$uid];// 查找用户昵称
    }else{
        $info = Db::name('Member')->where('uid', $uid)->field('nickname,uid')->find(); //获取用户信息
        if (empty($info)) {
            return null;
        }
        $list[$info['uid']] = $info['nickname'];
        Cache::set('sys_user_nickname_list', $list);
    }
    //缓存用户
    $count = count($list);
    $max = Config::get('key.user_max_cache') ?: 500;
    while ($count--> $max) {
        array_shift($list);
    }
    return $info['nickname'] ?? '';
}

/**
 * 二维数组重新降维成一位数组
 * @param array $array 二维数组
 * @author staitc7 <static7@qq.com>
 * @return array|null
 */
function one_dimensional($array) {
    if (empty($array)) {
        return null;
    }
    $tmp = [];
    foreach ($array as $k => $v) {
        $tmp[$v['name']] = $v['id'];
    }
    return $tmp;
}

/**
 * 获取行为类型
 * @param intger $type 类型
 * @param bool $all 是否返回全部类型
 * @author huajie <banhuajie@163.com>
 * @return array|mixed
 */
function get_action_type($type=null, $all = false) {
    $list = Config::get('config.action_type');
    if ($all) {
        return $list;
    }
    return $list[$type];
}

/**
 *  分析枚举类型配置值
 *  格式 a:名称1,b:名称2
 * @param string $string 配置值
 * @return array
 */
function parse_config_attr($string) {
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if (strpos($string, ':')) {
        $value = [];
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k] = $v;
        }
    } else {
        $value = $array;
    }
    return $value;
}