<?php
/**
 * Description of CloudTencent.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/9/27 17:25
 */

namespace app\admin\controller;

use app\admin\traits\Admin;
use traits\controller\Jump;
use CloudTencent\Cos\Api;
use think\Container;

class CloudTencent
{
    use Admin,Jump;

    /**
     * 绑定容器
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    protected function bindContainer()
    {
        $config=$this->app->config->get('config.cloud_tencent');
        return Container::getInstance()->bind('cos', new Api($config));
    }

    /**
     * 腾讯云上传
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function index()
    {
        $list=$this->app->cos->listFolder('/');
        dump($list['data']);die;
    }
}