<?php
/**
 * Description of Home.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-04-28 19:10
 */

namespace app\admin\traits;

use Auth\Auth;
use app\facade\UserInfo;
use think\{
    Container,Facade,Db
};

trait Admin
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
        UserInfo::userId() || $this->redirect('Login/index');
        $this->app = Container::getInstance()->make('think\App');
        //绑定其他类到容器
        $this->bindContainer();
    }

    /**
     * 其他类绑定到容器
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    protected function bindContainer()
    {

    }

    /**
     * 设置视图并输出
     * @author staitc7 <static7@qq.com>
     * @param array  $value 赋值
     * @param string $template 模板名称
     * @param bool   $menus 菜单
     * @return mixed
     */
    protected function setView(array $value = [], string $template = '',$menus=true)
    {
        //模板初始化
        $this->view = $this->view ?: Facade::make('view')->init($this->app->config->pull('template'));
        $this->view->config('tpl_replace_string', $this->app->config->get('config.view_replace'));
        //开启系统菜单
        $menus && $this->view->assign('systemMenus', $this->getMenus());
        return $this->view->fetch($template ?: '', $value ?: []);
    }

    /**
     * ajax返回格式
     * @author staitc7 <static7@qq.com>
     * @param mixed   $data 返回的数据
     * @param integer $code 状态码
     * @param array   $header 头部
     * @param array   $options 参数
     * @return mixed
     */
    protected function json($data = [], $code = 200, $header = [], $options = [])
    {
        return $this->app->response->create($data, 'json', $code, $header, $options);
    }

    /**
     * layui 专用返回数据格式
     * @author staitc7 <static7@qq.com>
     * @param array|null  $data 返回的数据
     * @param int         $code 状态码
     * @param null|string $msg 状态信息
     * @return mixed
     */
    protected function layuiJson(array $data = [], int $code = 0, string $msg = '')
    {
        if (empty($data) || !isset($data['total']) || empty($data['data'])) {
            $data = ['code' => 1, 'msg' => '暂时没有数据', 'count' => 0, 'data' => ''];
        } else {
            $data = ['code' => $code, 'msg' =>$msg, 'count' => $data['total'], 'data' => $data['data']];
        }
        return $this->json($data);
    }

    /**
     * 获取控制器菜单数组,二级菜单元素位于一级菜单的'_child'元素中
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    final public function getMenus() {
        if ($this->app->request->isAjax()) { //ajax 跳过
            return false;
        }
        $controller = strtolower($this->app->request->controller());//当前的控制器名
        if ($controller=="admin"){
            return $this->error("未授权访问");
        }
//        $menus = $this->app->session->get('admin_meun_list.' . $controller, 'menu');
//        if ($menus) {
//            return $menus;
//        }
        $module = strtolower($this->app->request->module());//当前的模块名
        $action = strtolower($this->app->request->action());//当前的操作名
        $where = [
            ['pid' ,'=', 0],
            ['hide','=',0],
            ['status','<>', -1],
            ['is_dev','=',(int)$this->app->config->get('admin_config.develop_mode')]
        ];
        $menus['main'] = Db::name("menu")->where($where)->order('sort asc')->field('id,title,url')->select(); // 获取主菜单
        $menus['child'] = []; //设置子节点
        foreach ($menus['main'] as $key => $item) {
            $item['url']=strtolower($item['url']);
            // 判断主菜单权限
            if (!UserInfo::isAdmin() && !$this->checkRule("{$module}/{$item['url']}", $this->app->config->get('config.auth_rule.rule_main'), null)) {
                unset($menus['main'][$key]);
                continue; //继续循环
            }
            "{$controller}/{$action}" == $item['url'] ? $menus['main'][$key]['class'] = 'layui-this' : null;
        }
        $map = [['pid', '<>', 0], ['url' ,'=', "{$controller}/{$action}"]]; // 查找当前子菜单
        $pid = Db::name("menu")->where($map)->value('pid');
        if ($pid) {
            $tmp_pid = Db::name("menu")->field('id,pid')->find($pid);
            $nav = $tmp_pid['pid'] ? Db::name("menu")->field('id,pid')->find($tmp_pid['pid']) : $tmp_pid; // 查找当前主菜单
            foreach ($menus['main'] as $key => $item) {
                if ((int)$item['id'] === (int)$nav['id']) {// 获取当前主菜单的子菜单项
                    $menus['main'][$key]['class'] = 'layui-this';
                    $groups = Db::name("menu")->where([['group','<>', ''], ['pid' ,'=', $item['id']]])->distinct(true)->column("group"); //生成child树
                    $second_urls = Db::name("menu")->where('pid',$item['id'])->field('id,url')->select() ?: []; //获取二级分类的合法url
                    $to_check_urls = $this->toCheckUrl($second_urls); // 检测菜单权限
                    foreach ($groups as $g) {// 按照分组生成子菜单树
                        $where=[['pid','=',$item['id']],['group','=',$g],['status','<>', -1]];
                        if (isset($to_check_urls) && !empty($to_check_urls)) {
                            $where[] = ['url','in', $to_check_urls];
                        }
                        $menuList = Db::name("menu")->where($where)->field('id,pid,title,url,tip')->order('sort asc')->select();
                        $menus['child'][$g] = list_to_tree($menuList, 'id', 'pid', 'operater', $item['id']);
                    }
                }
            }
        }

        $this->app->session->set('admin_meun_list.' . $controller, $menus, 'menu');
        return $menus;
    }

    /**
     * 非超级管理员的权限检测
     * @param array $second_urls
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    private function toCheckUrl(array $second_urls = []) {
        // 检测菜单权限
        if (empty(UserInfo::userId())) {
            return null;
        }
        $module = $this->app->request->module();
        $to_check_urls = [];
        if (empty($second_urls)){
            return null;
        }
        foreach ($second_urls as $key => $to_check_url) {
            if (stripos($to_check_url['url'], $module) !== 0) {
                $rule = "{$module}/{$to_check_url['url']}";
            } else {
                $rule = $to_check_url['url'];
            }
            if ($this->checkRule($rule, $this->app->config->get('config.auth_rule.rule_url'), null)) {
                $to_check_urls[] = $to_check_url['url'];
            }
        }
        return empty($to_check_urls) ? null : $to_check_urls;
    }
    /**
     * 权限检测
     * @param string $rule 检测的规则
     * @param null $type
     * @param string $mode check模式
     * @return bool
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    final private function checkRule($rule, $type = null, $mode = 'url') {
        static $Auth_static = null;
        $Auth = $Auth_static ?? new Auth();
        $type = $type ? $type : $this->app->config->get('config.auth_rule.rule_url');
        if (!$Auth->check($rule, UserInfo::userId(), $type, $mode)) {
            return false;
        }
        return true;
    }


    /**
     * 通用排序更新
     * @param int $id 菜单ID
     * @param int $sort 排序
     * @author staitc7 <static7@qq.com>
     */
    public function currentSort($id = 0, $sort = null) {
        (int) $id || $this->error('参数错误');
        is_numeric((int) $sort) || $this->error('排序非数字');
        $info =$this->app->model($this->app->request->controller())->setStatus(['id' => $id], ['sort' => (int) $sort]);
        return $info !== false ?
            $this->success('排序更新成功') :
            $this->error('排序更新失败');
    }


    /**
     * 通用单条数据状态修改
     * @param int $value 状态
     * @param null $ids
     * @internal param ids $int 数据条件
     * @author staitc7 <static7@qq.com>
     */
    public function setStatus($value = null, $ids = null) {
        empty($ids) && $this->error('请选择要操作的数据');
        is_numeric((int) $value) || $this->error('参数错误');
        $info = $this->app->model($this->app->request->controller())
            ->setStatus([['id','in', $ids]], ['status' => $value]);
        return $info !== false ?
            $this->success($value == -1 ? '删除成功' : '更新成功') :
            $this->error($value == -1 ? '删除失败' : '更新失败');
    }

    /**
     * 通用批量数据更新
     * @param int $value 状态
     * @author staitc7 <static7@qq.com>
     */
    public function batchUpdate($value = null) {
        $ids = $this->app->request->post();
        empty($ids['ids']) && $this->error('请选择要操作的数据');
        is_numeric((int) $value) || $this->error('参数错误');
        $info = $this->app->model($this->app->request->controller())
            ->setStatus([['id','in', $ids['ids']]], ['status' => $value]);
        return $info !== false ?
            $this->success($value == -1 ? '删除成功' : '更新成功') :
            $this->error($value == -1 ? '删除失败' : '更新失败');
    }



}
