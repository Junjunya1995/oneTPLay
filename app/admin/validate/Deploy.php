<?php
/**
 * Description of Deploy.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/9/18 12:43
 */

namespace app\admin\validate;


use think\Validate;

class Deploy extends Validate
{
    protected $rule = [
        'title' => "require|max:30",
        'name' => "require|unique:deploy,name"
    ];
    protected $message = [
        'title.require' => '标题不能为空',
        'title.max' => '标题最多不能超过30个字符',
        'name.require' => '配置名称不能为空',
        'name.unique' => '配置名称已经存在'
    ];
}