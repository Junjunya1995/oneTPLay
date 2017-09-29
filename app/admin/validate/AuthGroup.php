<?php
/**
 * Description of AuthGroup.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/9/18 12:42
 */

namespace app\admin\validate;


use think\Validate;

class AuthGroup extends Validate
{
    protected $rule = [
        'title' => "require|max:20"
    ];
    protected $message = [
        'title.require' => '标题不能为空',
        'title.max' => '标题最多不能超过20个字符'
    ];
}