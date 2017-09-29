<?php
/**
 * Description of Notice.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/8/3 21:40
 */

namespace app\admin\controller;


use app\admin\traits\Admin;
use traits\controller\Jump;
use app\facade\PictureUpload;

class Notice
{
    use Admin, Jump;

    /**
     * 公告列表
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function index()
    {
        return $this->setView(['metaTitle ' => '通知公告列表']);
    }

    /**
     * 公告数据列表
     * @author staitc7 <static7@qq.com>
     * @param int $page 页码
     * @param int $limit 每页条数
     * @return mixed
     */
    public function noticeJson($page = 1, $limit = 10)
    {
        $data = $this->app->model('Notice')->listsJson([['status', '<>', -1]], null, 'create_time desc', (int)$page ?: 1, $limit);
        return $this->layuiJson($data);
    }

    /**
     * 公告详情
     * @param int $id 公告ID
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */

    public function edit($id = 0)
    {
        if ((int)$id > 0) {
            $info = $this->app->model('Notice')->edit($id);
        }
        return $this->setView(['info' => $info ?? null, 'metaTitle' => '通知公告详情']);
    }

    /**
     * 用户更新或者添加公告
     * @author staitc7 <static7@qq.com>
     */

    public function renew()
    {
        $Notice = $this->app->model('Notice');
        $info   = $Notice->renew();
        if ($info === false) {
            return $this->error($Notice->getError());
        }
        $this->app->cache->rm('notice');
        return $this->success('操作成功', $this->app->url->build('Notice/index'));
    }

    /**
     * 图片上传
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function fileUpload()
    {
        $jsonData = [
            'code' => 0, 'msg' => '', 'data' => []
        ];
        $data = PictureUpload::upload('file');

        if ($data === false) {
            $jsonData['code'] = 1;
            $jsonData['msg']  = PictureUpload::getError();
            return $jsonData;
        }
        $jsonData['data'] = [
            'src' => $data['path'], 'title' => $data['original_name']
        ];
        return $jsonData;
    }

    /**
     * 附件上传
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function annex()
    {
        // TODO 待完善
        $jsonData = [
            'code' => 0, 'msg' => '', 'data' => []
        ];
        $data = PictureUpload::upload('file');

        if ($data === false) {
            $jsonData['code'] = 1;
            $jsonData['msg']  = PictureUpload::getError();
            return $jsonData;
        }
        $jsonData['data'] = [
            'src' => $data['path'], 'title' => $data['original_name']
        ];
        return $jsonData;
    }

}