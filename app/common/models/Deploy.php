<?php

namespace app\common\models;

use think\facade\Log;
use think\Model;

/**
 * Description of UserApi
 * 应用的系统配置API
 * @author static7
 */
class Deploy extends Model
{
    /**
     * 获取数据库中的配置列表
     * @param array $map_tmp 临时条件
     * @return array 配置数组
     * @internal param $map
     */
    public function lists($map_tmp = [])
    {
        $map = [
            ['status', '=', 1],
            $map_tmp
        ];
        //合并条件
        $object = $this::all(function ($query) use ($map) {
            $query->where($map)->field('type,name,value');
        });
        $config = [];
        if (is_object($object)) {
            $object = $object->toArray();
            foreach ($object as $value) {
                $config[ strtolower($value['name']) ] = $this->parse($value['type'], $value['value']);
            }
        }
        return $config;
    }

    /**
     * 根据配置类型解析配置
     * @param  integer $type 配置类型
     * @param  string  $value 配置值
     * @return array|string
     */
    private function parse($type, $value)
    {
        switch ($type) {
            case 2:
                $value=htmlspecialchars($value);
                break;
            case 3: //解析数组
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                if (strpos($value, ':')) {
                    $value = [];
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[ $k ] = $v;
                    }
                } else {
                    $value = $array;
                }
                break;
            case 5:
                 $value=(int)$value;
                break;
        }
        return $value;
    }

}
