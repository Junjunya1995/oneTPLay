<?php
/**
 * Description of Channel.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-08-04 15:24
 */

namespace app\admin\controller;


use app\admin\traits\Admin;
use traits\controller\Jump;

class Channel
{
    use Admin, Jump;

    /**
     * 导航列表
     * @param int $pid 父级ID
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */

    public function index($pid = 0)
    {
        $father  = $this->app->model('Channel')->father($pid); //查询父级ID
        $value   = [
            'pid' => $pid,
            'father' => $father ?: null,
            'metaTitle' => '导航列表'
        ];
        return $this->setView($value);
    }

    /**
     * 导航列表数据
     * @author staitc7 <static7@qq.com>
     * @param int $pid 父级ID
     * @param int $page 页码
     * @param int $limit 限制条数
     * @return mixed
     */
    public function channelJson($pid = 0, $page = 1, $limit = 10)
    {
        $data = $this->app->model('Channel')->listsPage([
                ['pid', '=', (int)$pid ?: 0],
                ['status', '<>', -1]
            ], null, 'sort asc,id asc', (int)$page ?: 1, $limit);
        return $this->layuiJson($data);
    }

    /**
     * 新增导航
     * @param int $pid 父级导航ID
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */

    public function add($pid = 0)
    {
        return $this->setView(['pid' => (int)$pid ?: 0, 'metaTitle' => '新增导航']);
    }

    /**
     * 编辑导航
     * @param int $id 导航ID
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */

    public function edit($id = 0)
    {
        (int)$id || $this->error('参数错误!');
        $info = $this->app->model('Channel')->edit($id);
        return $this->setView(['info' => $info ?: null, 'metaTitle' => '导航详情']);
    }

    /**
     * 用户更新或者添加导航
     * @author staitc7 <static7@qq.com>
     */

    public function renew()
    {
        $Channel = $this->app->model('Channel');
        $info    = $Channel->renew();
        if ($info===false) {
            return $this->error($Channel->getError());
        }
        $this->app->cache->rm('menu_list');
        return $this->success('操作成功', $this->app->url->build('Channel/index', ['pid' => $info['pid'] ?: 0]));
    }
}