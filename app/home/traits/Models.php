<?php
/**
 * Description of models.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-04-29 18:25
 */

namespace app\home\traits;

use think\facade\{
    Config
};

trait Models
{
    /**
     * @author staitc7 <static7@qq.com>
     * @param array  $map 条件
     * @param string $field 字段
     * @param string $order 排序
     * @param int    $limit 限制条数
     * @return mixed
     */
    public function lists(?array $map = [], ?string $field = '*', ?string $order = '', int $limit = 0)
    {

        return $this::all(function ($query) use ($map, $field, $order, $limit) {
            $query->where($map ?: [])->field($field ?: '*')->order($order ?: $this->pk.' ASC')->limit($limit ?: Config::get('config.list_rows'));
        });
        return $object? $object->toArray() : false;
    }

    /**
     * 数据分页
     * @author staitc7 <static7@qq.com>
     * @param array  $map 条件
     * @param string $field 字段
     * @param string $order 排序
     * @param int    $limit 限制条数
     * @param int    $page 当前页面
     * @param array $query 额外参数
     * @return mixed
     */
    public function listsPage(?array $map = [], ?string $field = '*', ?string $order = '', int $page = 1, int $limit = 10, array $query = [])
    {
        $object = $this::where($map ?: [])
            ->field($field ?: '*')
            ->order($order ?: $this->pk.' ASC')
            ->paginate([
            'page' => $page,
            'query' => $query,
            'list_rows' => $limit ?: Config::get('config.list_rows')
        ]);
        return $object ? array_merge($object->toArray(),['page'=>$object->render()]) : false;
    }
}