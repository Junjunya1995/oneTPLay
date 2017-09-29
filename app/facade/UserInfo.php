<?php
/**
 * Description of UserInfo.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-07-27 15:23
 */

namespace app\facade;

use think\Facade;

class UserInfo extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\internal\UserInfo';
    }
}