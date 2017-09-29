<?php
/**
 * Description of Channel.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-08-04 15:24
 */

namespace app\admin\model;

use app\admin\traits\Models;
use think\{
    Model, Validate
};
use think\facade\{
    Request
};

class Channel extends Model
{
    use Models;
    protected $auto   = ['title', 'url'];
    protected $insert = ['status' => 1];

    /**
     * 查询父级导航
     * @param int $pid 父级导航ID
     * @author staitc7 <static7@qq.com>
     * @return array
     */

    public function father(int $pid = 0): array
    {
        $object = $this::get(function ($query) use ($pid) {
            $query->where('id', $pid)->field('pid,title');
        });
        return $object ? $object->toArray() : [];
    }


}