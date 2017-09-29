<?php
/**
 * Description of FileUpload.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/9/28 16:12
 */

namespace app\facade;


use think\Facade;

class FileUpload extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\internal\FileUpload';
    }
}