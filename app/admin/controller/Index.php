<?php

namespace app\admin\controller;

use app\admin\traits\Admin;
use traits\controller\Jump;
use think\facade\{
    Hook
};
use app\facade\UserInfo;

/**
 * 后台首页
 * Class Index
 * @package app\admin\controller
 */
class Index
{
    //引入容器
    use Admin, Jump;

    public function index()
    {
        return $this->setView();
    }


    /**
     * 清空文件缓存
     * @param string $path 缓存路径
     * @return bool
     */
    function clearRuntime(?string $path = null)
    {
        $path  = $path ?: $this->app->getRuntimePath();
        $files = scandir($path);
        if ($files) {
            foreach ($files as $file) {
                if ('.' != $file && '..' != $file && is_dir($path . $file)) {
                    array_map('unlink', glob($path . $file . '/*.*'));
                } elseif ('.gitignore' != $file && is_file($path . $file)) {
                    unlink($path . $file);
                }
            }
        }
       return  $this->success('已经成功清理!');
    }

}
