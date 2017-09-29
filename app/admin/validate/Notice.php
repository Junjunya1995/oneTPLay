<?php
/**
 * Description of Notice.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/9/18 12:45
 */

namespace app\admin\validate;


use think\Validate;

class Notice extends Validate
{
    protected $rule = [
        'title' => "require|max:30",
        'content' => "require",
        'description'=>"max:200"
    ];
    protected $message = [
        'title.require' => '标题不能为空',
        'title.max' => '标题最多不能超过30个字符',
        'description.max' => '描述标题不能超过200个字',
        'content.require' => '内容不能为空',
    ];
}