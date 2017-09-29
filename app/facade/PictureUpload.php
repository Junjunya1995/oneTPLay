<?php
/**
 * Description of PictureUpload.phpphp.
 * User: static7 <static7@qq.com>
 * Date: 2017/9/18 22:56
 */

namespace app\facade;

use think\Facade;

class PictureUpload extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\internal\PictureUpload';
    }

}