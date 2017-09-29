<?php
/**
 * Description of Tree.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-08-08 15:59
 */

namespace app\facade;


use think\Facade;

class Tree extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\internal\Tree';
    }
}