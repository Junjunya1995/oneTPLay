<?php
/**
 * Description of Action.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-07-25 15:41
 */

namespace app\admin\controller;


use app\admin\traits\Admin;
use think\Db;
use traits\controller\Jump;

class Action
{

    use Admin,Jump;

    /**
     * 用户行为
     * @author staitc7 <static7@qq.com>

     * @return mixed
     */
    public function index()
    {
        return $this->setView(['metaTitle'=>'用户行为']);
    }

    /**
     * 用户行为json
     * @author staitc7 <static7@qq.com>
     * @param int $page 页码
     * @param int $limit 条数
     * @return mixed
     */
    public function actionJson($page=1,$limit=10)
    {
        $data=$this->app->model('Action')->listsjson([['status','<>',-1]],null,'status desc,id desc',(int)$page ?:1,$limit);
        return $this->layuiJson($data);
    }

    /**
     * 新增或者编辑行为
     * @param int $id 行为ID
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */

    public function edit($id = 0) {
        if ((int)$id > 0) {
            $data = $this->app->model('Action')->edit($id);
        }
        return $this->setView(['info'=>$data ?? null,'metaTitle'=>(int)$id > 0 ? '编辑' : '新增' . '行为']);
    }

    /**
     * 用户更新或者添加行为
     * @author staitc7 <static7@qq.com>
     */

    public function renew() {
        $Action = $this->app->model('Action');
        $info=$Action->renew(null,true);
        if ($info===false) {
            return $this->error($Action->getError());
        }
        return $this->success('操作成功', $this->app->url->build('Action/index'));
    }

    /**
     * 行为日志
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function actionLog()
    {
        return $this->setView(['metaTitle'=>'行为日志'],'action_log');
    }

    /**
     * 行为日志列表
     * @author staitc7 <static7@qq.com>
     * @param int $page 页码
     * @param int $limit 每页数据量
     * @return mixed
     */
    public function actionLogJson($page=1,$limit=10)
    {
        $data = $this->app->model('ActionLog')->listsJson([['status','=',1]], null, 'create_time desc', (int)$page ?: 1,(int)$limit);
        return $this->layuiJson($data);
    }

    /**
     * 行为日志详细
     * @param int $id 日志ID
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */

    public function detailed($id = 0) {
        (int)$id || $this->error('参数错误');
        $info = $this->app->model('ActionLog')->edit((int)$id);
        empty($info) && $this->error('该条日志不存在');
        return $this->setView(['info' => $info,'metaTitle'=> '行为日志详情']);
    }

    /**
     * 删除日志
     * @param mixed $ids
     * @author huajie <banhuajie@163.com>
     */
    public function remove($ids = 0) {
        (int)$ids || $this->error('参数错误！');
        if (is_array($ids)) {
            $map = [['id','in', $ids]];
        } elseif (is_numeric((int)$ids)) {
            $map = [['id','=',$ids]];
        }
        $res = Db::name('ActionLog')->where($map)->setField('status',-1);
        return $res !== false ? $this->success('删除成功') : $this->error('删除失败');
    }

    /**
     * 清空日志
     */
    public function clear() {
        $res = Db::name('ActionLog')->where('1=1')->delete();
        return $res !== false ?
            $this->success('日志清空成功！') :
            $this->error('日志清空失败！');
    }


}