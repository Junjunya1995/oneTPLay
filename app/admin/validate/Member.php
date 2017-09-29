<?php
/**
 * Description of Member.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/9/18 12:44
 */

namespace app\admin\validate;


use think\Validate;

class Member extends Validate
{
    protected $rule = [
        'nickname' => 'require|unique:member,nickname',
    ];
    protected $message = [
        'nickname.require' => '昵称不能为空',
        'nickname.unique' => '昵称被占用，换一个吧',
    ];
}