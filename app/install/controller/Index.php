<?php

namespace app\install\controller;

use think\{
    Container,Facade
};
use think\facade\{
    Env
};
use Storage\Storage;
use traits\controller\Jump;

class Index {

    //引入jump类
    use Jump;

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
        //绑定Storage到容器
        Container::getInstance()->bind('storage',Storage::class);
        //模板初始化
        $this->view = $this->view ?: Facade::make('view')->init(
            $this->app->config->pull('template'),//模板引擎
            $this->app->config->get('config.view_replace') //替换参数
        );
    }

    /**
     * 安装首页
     * @author static7 <static7@qq.com>
     * @return type
     */
    public function index() {
        if (is_file(Env::get('config_path'). 'database.php')) {
            $this->app->session->set('update',1, 'install'); // 已经安装过了 执行更新程序
            $msg = '请删除'.Env::get('root_path').'data/install.lock文件后再运行升级!';
        } else {
            $msg = '已经成功安装了本系统，请不要重复安装!';
        }
        if (is_file(Env::get('root_path') . 'data/install.lock')) {
            return $this->error($msg,$this->app->url->build('index'));
        }
        return $this->view->fetch('',['step'=>'layui-this']);
    }

    /**
     * 安装完成
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function complete() {
        $step = $this->app->session->get('step', 'install');
        if (!$step) {
            $this->redirect('index');
        } elseif ($step != 3) {
            return $this->redirect("Install/step{$step}");
        }
        // 写入安装锁定文件
        $this->app->storage->put(Env::get('root_path') . 'data/install.lock', 'lock');
        if (!$this->app->session->get('update','install')) {
            //创建配置文件
            $this->view->assign('info', $this->app->session->get('config_file','install'));
        }
        $this->app->session->delete('step', 'install');
        $this->app->session->delete('error', 'install');
        $this->app->session->delete('update', 'install');
        return $this->view->fetch('',['step4'=>'layui-this']);
    }

}
