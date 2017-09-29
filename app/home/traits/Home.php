<?php
/**
 * Description of Home.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-04-28 19:10
 */

namespace app\home\traits;

use think\{
    Container,Facade
};
use app\facade\UserInfo;

trait Home
{
    //容器实例
    protected $app;
    //视图
    protected $view;

    /**
     * 初始化容器
     */
    public function __construct()
    {
        $this->app = Container::getInstance()->make('think\App');
    }

    /**
     * 设置视图并输出
     * @param array  $value 赋值
     * @param string $template 模板名称
     * @return mixed
     */
    protected function setView(?array $value = [], ?string $template = '')
    {
        //模板初始化
        $this->view = $this->view ?: Facade::make('view')->init(
            $this->app->config->pull('template'),//模板引擎
            $this->app->config->get('config.view_replace') //替换参数
        );
        //开启系统菜单
        return $this->view->fetch($template ?: '', $value ?: []);
    }

    /**
     * ajax返回格式
     * @param mixed   $data 返回的数据
     * @param integer $code 状态码
     * @param array   $header 头部
     * @param array   $options 参数
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    protected function json($data = [], $code = 200, $header = [], $options = [])
    {
        return $this->app->response->create($data, 'json', $code, $header, $options);
    }
}