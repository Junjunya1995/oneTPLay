<?php
/**
 * Description of Action.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-07-25 15:42
 */

namespace app\admin\model;


use app\admin\traits\Models;
use think\{
    Model,Db
};


class ActionLog extends Model
{
    use Models;

    /*====================修改器====================*/
    /**
     * 行为名称
     * @author staitc7 <static7@qq.com>
     * @param $value 值
     * @return mixed
     */
    public function getActionIdAttr($value)
    {
        return empty($value) ? null : $this->buildQuery()->name('Action')->where('id',$value)->value('title');
    }

    /**
     * 执行者
     * @author staitc7 <static7@qq.com>
     * @param $value 值
     * @return mixed
     */
    public function getUserIdAttr($value)
    {
        return $value ? get_nickname((int)$value) :$value;
    }

    /**
     * 状态转换
     * @param $value 值
     * @return mixed
     */
    public function getTypeAttr($value)
    {
        return $value ? change_status($value,[1=>'更新',2=>'添加']) : null;
    }
}