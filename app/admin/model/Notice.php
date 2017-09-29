<?php
/**
 * Description of Notice.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/8/3 21:40
 */

namespace app\admin\model;


use app\admin\traits\Models;
use think\Model;

class Notice extends Model
{
    use Models;
    protected $autoWriteTimestamp = true; //自动写入创建和更新的时间戳字段
    protected $auto = ['title', 'content'];
    protected $insert = ['status' => 1,];

    /*===============获取器=================*/

    /**
     * 描述
     * @author staitc7 <static7@qq.com>
     * @param $value
     * @return mixed
     */
    public function getDescriptionAttr($value)
    {
        return $value ? msubstr($value,0,70):'';
    }

    /**
     * 描述
     * @author staitc7 <static7@qq.com>
     * @param $value
     * @return mixed
     */
    public function getTypeAttr($value)
    {
        return is_numeric($value) ? change_status($value,['前后台','前台','后台']):'';
    }

}