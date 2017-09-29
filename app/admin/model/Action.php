<?php
/**
 * Description of Action.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-08-02 11:34
 */

namespace app\admin\model;


use app\admin\traits\Models;
use think\Model;

class Action extends Model
{
    use Models;
    protected $autoWriteTimestamp = true; //自动写入创建和更新的时间戳字段
    protected $createTime = false;
    protected $auto = ['title'];
    protected $insert = [
        'status' => 1
    ];

    /*================获取器================*/

    /**
     * 类型
     * @author staitc7 <static7@qq.com>
     * @param $value
     * @return mixed
     */
    public function getTypeAttr($value)
    {
        return $value ? get_action_type($value):null;
    }


    /*================修改器================*/

    /**
     * 配置名称过滤
     * @author staitc7 <static7@qq.com>
     * @param $value 修改的值
     * @return string
     */

    protected function setTitleAttr($value) {
        return $value ? htmlspecialchars($value) : null;
    }
}