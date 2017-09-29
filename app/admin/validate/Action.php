<?php
/**
 * Description of Action.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/9/18 12:41
 */

namespace app\admin\validate;


use think\Validate;

class Action extends Validate
{
    protected $rule = [
        'title' => "require|max:30",
        'name' => "require|alphaDash|unique:action,name",
        'remark' => 'max:140'
    ];
    protected $message = [
        'title.require' => '行为标题不能为空',
        'title.max' => '行为标题最多不能超过30个字符',
        'name.require' => '行为名称不能为空',
        'name.unique' => '行为名称已经存在',
        'name.alphaDash' => '行为标识为字母和数字，下划线_及破折号-',
        'remark.require' => '行为描述不能为空',
        'remark.max' => '行为描述不能超过140个字符'
    ];
    protected $scene = [
        'edit'  =>  ['title','name' => "require|alphaDash"],
    ];
}