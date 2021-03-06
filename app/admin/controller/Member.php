<?php
/**
 * Description of Member.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/9/19 22:54
 */

namespace app\admin\controller;


use app\admin\traits\Admin;
use traits\controller\Jump;
use app\facade\{
    Condition,UserInfo
};

class Member
{
    use Admin,Jump;

    /**
     * 用户首页
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function index()
    {
        return $this->setView(['metaTitle' => '会员管理']);
    }

    /**
     * 用户
     * @author staitc7 <static7@qq.com>
     * @param int $page 当前页
     * @param int $limit 每页条数
     * @return mixed
     */
    public function userJson($page = 1,$limit=10)
    {
        if (!$this->app->request->isAjax()) {
            return $this->json('系统错误!,请重新刷新页面');
        }
        $data = $this->app->model('Member')->listsJson([['status','<>',-1]], null, null, (int)$page ?: 1,$limit);
        return $this->layuiJson($data);
    }

    /**
     * 添加用户
     * @author staitc7 <static7@qq.com>
     */
    public function add() {
        return $this->setView(['metaTitle' => '添加会员']);
        return $this->success('添加成功', $this->app->url->build('index'));
    }

    /**
     * 添加用户
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function renew()
    {
        $Member =$this->app->model('Member');
        $info=$Member->userAdd();
        if ($info===false) {
            return $this->error($Member->getError());
        }
    }


    /**
     * 编辑用户
     * @author staitc7 <static7@qq.com>
     * @param int $uid
     * @return mixed
     */
    public function edit($uid=0)
    {
        if ((int)$uid>0){
            $info=$this->app->model('Member')->edit($uid);
        }
        return $this->setView(['info'=>$info ?? null],'detail');
    }

    /**
     * 单条数据状态修改
     * @param int $value 状态
     * @param null $ids
     * @internal param ids $int 数据条件
     * @author staitc7 <static7@qq.com>
     */
    public function setStatus($value = null, $ids = null) {
        empty($ids) && $this->error('请选择要操作的数据');
        if (UserInfo::isAdmin((int)$ids)) {
            return $this->error('不允许对超级管理员执行该操作');
        }
        is_numeric((int)$value) || $this->error('参数错误');
        $info = $this->app->model('Member')->setStatus([['uid','in', (int)$ids]], ['status' => $value]);
        return $info !== false ?
            $this->success($value == -1 ? '删除成功' : '更新成功') :
            $this->error($value == -1 ? '删除失败' : '更新失败');
    }

    /**
     * 批量数据更新
     * @param int $value 状态
     * @author staitc7 <static7@qq.com>
     */
    public function batchUpdate($value = null) {
        $ids = array_unique($this->app->request->post()['ids']);
        empty($ids) && $this->error('请选择要操作的数据');
        if (in_array((string)UserInfo::isAdmin(), $ids, true)) {
            $this->error('用户中包含超级管理员，不能执行该操作');
        }
        !is_numeric((int)$value) && $this->error('参数错误');
        $info = $this->app->model('Member')->setStatus([['uid','in', $ids]], ['status' => $value]);
        return $info !== false ?
            $this->success($value == -1 ? '删除成功' : '更新成功') :
            $this->error($value == -1 ? '删除失败' : '更新失败');
    }
}