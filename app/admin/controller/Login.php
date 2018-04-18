<?php
/**
 * Description of Login.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-07-11 16:48
 */

namespace app\admin\controller;

use app\facade\UserInfo;
use Geetest\Geetest;
use think\Container;
use think\Facade;
use think\facade\Log;
use traits\controller\Jump;

class Login
{

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
        //模板初始化
        $this->view = $this->view ?: Facade::make('view')->init($this->app->config->pull('template'));
        $this->view->config('tpl_replace_string', $this->app->config->get('config.view_replace'));
    }

    /**
     * 登录页面
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function index()
    {
        return $this->view->fetch();
    }

    /**
     * 登录
     * @author staitc7 <static7@qq.com>
     * @param string $username 用户名
     * @param string $password 密码
     * @return mixed
     */
    public function login($username = '', $password = '')
    {
        //判断是否ajax登录
        $this->app->request->isPost() || $this->error('非法请求');
        //极验证 TODO 暂时撤销
//        $this->geetestValidate() || $this->error('验证失败,请稍候再试...');
        $user_id = $this->app->model('UcenterMember', 'models')->login($username, $password);
        //登录失败
        if ((int)$user_id < 0) {
            return $this->error($this->loginError($user_id));
        }
        //更新用户信息
        $Member = $this->app->model('Member');
        $info   = $Member->login($user_id);
        if ($info === false) {
            return $this->error($Member->getError());
        }
        //处理用户信息
        UserInfo::autoLogin($info);
        $this->success('登录成功', $this->app->url->build('Index/index'));

    }

    /**
     * 极验证
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function geetest()
    {
        $config  = $this->app->config->get('config.geetest');
        $geetest = new Geetest($config['captcha_id'], $config['private_key']);
        $data    = [
            "user_id" => "geetest_" . get_random(), # 网站用户id
            "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => $this->app->request->ip()
        ];
        $status  = $geetest->pre_process($data, 1);
        $this->app->session->set('gtserver',$status);
        $this->app->session->set('gt_user_id', $data['user_id']);
        return $this->app->response->create($geetest->get_response(), 'json', 200);
    }

    /**
     * 极验证的二次验证
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    private function geetestValidate()
    {
        $param = $this->app->request->only('geetest_challenge,geetest_validate,geetest_seccode');
        if (empty($param)) {
            return false;
        }
        $data    = [
            "user_id" => $this->app->session->get('gt_user_id'),
            "client_type" => "web",
            "ip_address" => $this->app->request->ip()
        ];
        $config  = $this->app->config->get('config.geetest');
        $Geetest = new Geetest($config['captcha_id'], $config['private_key']);
        if ((int)$this->app->session->get('gtserver') === 1) {
            return $Geetest->success_validate($param['geetest_challenge'], $param['geetest_validate'], $param['geetest_seccode'], $data);
        } else {
            return $Geetest->fail_validate($param['geetest_challenge'], $param['geetest_validate'], $param['geetest_seccode']);
        }
    }

    /**
     * 退出登录
     * @author staitc7 <static7@qq.com>
     */

    public function logout() {
        if (UserInfo::userId()<1) {
            return $this->redirect('Login/index');
        }
        UserInfo::logout();
        return $this->success('退出成功','Login/index');
    }

    /**
     * 登录错误信息
     * @param int $code 错误信息
     * @return string
     */
    private function loginError($code)
    {
        switch ($code) {
            case -1:
                $error = '用户不存在或被禁用！';
                break; //系统级别禁用
            case -2:
                $error = '用户名或者密码错误！';
                break;
            default:
                $error = '未知错误！';
                break; // 0-接口参数错误（调试阶段使用）
        }
        return $error;
    }

}