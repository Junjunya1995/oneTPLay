<?php
/**
 * Description of Links.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/8/3 21:32
 */

namespace app\admin\controller;

use app\admin\traits\Admin;
use traits\controller\Jump;

class Links
{
    use Admin, Jump;

    /**
     * 友情链接
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function index()
    {
        return $this->setView(['metaTitle' => '友情链接']);
    }

    /**
     * 友情链接数据列表
     * @author staitc7 <static7@qq.com>
     * @param int $page 页码
     * @param int $limit 限制条数
     * @return mixed
     */
    public function linksJson($page = 1,$limit=10)
    {
        $data = $this->app->model('Links')->listsPage([['status','<>', -1]], null, 'sort asc', (int)$page ?: 1,$limit);
        return $this->layuiJson($data);
    }

    /**
     * 排序更新
     * @param int $id 菜单ID
     * @param int $sort 排序
     * @author staitc7 <static7@qq.com>
     */
    public function sequence($id = 0, $sort = null) {
        (int)$id || $this->error('参数错误');
        !is_numeric((int)$sort) && $this->error('排序非数字');
        $info = $this->app->model('Links')->setStatus(['id' => $id], ['sort' => (int)$sort]);
        if ($info !== false) {
            $this->app->cache->rm("links");
            return $this->success('排序更新成功');
        } else {
            return $this->error('排序更新失败');
        }
    }

    /**
     * 编辑导航
     * @author staitc7 <static7@qq.com>
     * @param int $id 导航ID
     * @return mixed
     */

    public function edit($id = 0) {
        if ((int)$id >0){
            $info = $this->app->model('Links')->edit((int)$id);
        }
        return $this->setView(['info'=> $info ?? null,'metaTitle' => '友情链接详情']);
    }

    /**
     * 用户更新或者添加链接
     * @author staitc7 <static7@qq.com>
     */

    public function renew() {
        $Links = $this->app->model('Links');
        $info = $Links->renew();
        if ($info===false) {
            return $this->error($Links->getError());
        }
        $this->app->cache->rm('links');
        return $this->success('操作成功', $this->app->url->build('Links/index'));
    }

}