<?php

namespace app\install\controller;

use think\{
    Container,Facade,Db
};

use traits\controller\Jump;

class Install {

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
        $this->checkDatabase();
        //模板初始化
        $this->view = $this->view ?: Facade::make('view')->init(
            $this->app->config->pull('template'),//模板引擎
            $this->app->config->get('config.view_replace') //替换参数
        );
    }

    /**
     *
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function checkDatabase()
    {
        if (is_file(\Env::get('config_path') . 'database.php')) {
            return $this->error('已经成功安装了本系统，请不要重复安装!',$this->app->url->build('index'));
        }
    }
    /**
     * 安装第一步，检测运行所需的环境设置
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function step1() {
        $this->app->session->set('error', false, 'install');
        $this->app->session->set('step', 1, 'install');
        return $this->view->fetch('',[
            'step1'=>'layui-this',
            'env' => check_env() ?: null,//环境检测
            'func' => check_func() ?: null,//函数检测
            'dirfile' => check_dirfile() ?: null//目录文件读写检测
        ]);
    }

    /**
     * 安装第二步，创建数据库
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function step2() {
        if ($this->app->request->isGet()) {
            if ($this->app->session->get('update', 'install')) {
                $this->app->session->set('step', 2, 'install');
                return $this->view->fetch('update');
            } else {
                $this->app->session->get('error', 'install') && $this->error('环境检测没有通过，请调整环境后重试！');
                $step = $this->app->session->get('step', 'install');
                if ($step != 1 && $step != 2) {
                    return $this->redirect('step1');
                }
                $this->app->session->set('step', 2, 'install');
                return $this->view->fetch('',['step2'=>'layui-this']);
            }
        }
        $data = $this->app->request->post();
        $admin = $data['admin'];
        $db = $data['db'];
        //检测管理员信息
        if (!is_array($admin) || empty($admin[0]) || empty($admin[1]) || empty($admin[3])) {
            return $this->error('请填写完整管理员信息');
        } else {
            if ($admin[1] != $admin[2]) {
                return $this->error('确认密码和密码不一致');
            } else {
                $info = [];
                list($info['username'], $info['password'], $info['repassword'], $info['email']) = $admin;
                $this->app->session->set('admin_info', $info, 'install'); //缓存管理员信息
            }
        }
        //检测数据库配置
        if (!is_array($db) || empty($db[0]) || empty($db[1]) || empty($db[2]) || empty($db[3])) {
            return $this->error('请填写完整的数据库配置');
        } else {
            $DB = [];
            list($DB['type'], $DB['hostname'], $DB['database'], $DB['username'], $DB['password'], $DB['hostport'], $DB['prefix']) = $db;
            //缓存数据库配置
            $this->app->session->set('db_config', $DB, 'install');

            //创建数据库
            $dbname = $DB['database'];
            unset($DB['database']);
            $db = Db::connect($DB);
            $sql = "CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8";
            $db->execute($sql) || $this->error($db->getError());
        }
        //跳转到数据库安装页面
        return $this->redirect('step3');
    }

    /**
     * 安装第三步，安装数据表，创建配置文件
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function step3() {
        if ($this->app->session->get('step', 'install') != 2) {
            return $this->redirect('step2');
        }
        echo $this->view->fetch('',['step3'=>'layui-this']);
        if ($this->app->session->get('update', 'install') === 1) {
            show_msg('暂未开发!', 'error', $this->app->url->build('index'));
            //            update_tables(Db::connect(), $this->app->config->get('database.prefix'));//更新数据表
        } else {
            //连接数据库
            $dbconfig = $this->app->session->get('db_config', 'install');
            $db = Db::connect($dbconfig);
            //创建数据表
            create_tables($db, $dbconfig['prefix']);
            //创建配置文件
            $auth = build_auth_key();
            $conf = write_config($dbconfig, $auth);
            $this->app->session->set('config_file', $conf, 'install');
            //注册创始人帐号
            $admin = $this->app->session->get('admin_info', 'install');
            register_administrator($db, $dbconfig['prefix'], $admin, $auth);
            $status = $this->app->session->get('error', 'install');
            if ($status === true) {
                show_msg('安装失败,请检查运行环境', 'error');
            } else {
                $this->app->session->set('step', 3, 'install');
                show_msg('安装成功,页面即将跳转...', '', $this->app->url->build('Index/complete'));
            }
        }
    }

}
