<?php
/**
 * Description of Category.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-08-03 17:22
 */

namespace app\admin\controller;

use app\admin\traits\Admin;
use traits\controller\Jump;
use app\facade\PictureUpload;
use think\Db;

class Category
{
    use Admin,Jump;

    /**
     * 分类管理首页
     * @author staitc7 <static7@qq.com>
     */
    public function index() {
        $tree = $this->app->model('Category')->getTree(0, 'id,name,title,sort,pid,allow_publish,status');
        $this->app->config->set('_system_get_category_true_', true);
        return $this->setView(['tree' => $tree ?:null,'metaTitle' => '文章列表']);
    }

    /**
     * 显示分类树，仅支持内部调
     * @param  array $tree 分类树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     * @return mixed
     */
    public function tree($tree = null) {
        $this->app->config->get('_system_get_category_true_') || header("HTTP/1.1 404 Not Found");
        return $this->setView(['tree' => $tree],'tree',false);
    }

    /**
     * 新增分类
     * @author staitc7 <static7@qq.com>
     * @param int $pid 分类id
     * @return mixed
     */
    public function add($pid = 0) {
        $info = $this->app->model('Category')->info((int)$pid ?: 0, 'id,name,title,level');
        return $this->setView(['category' => $info ?:null,'metaTitle' => '新增分类'],'edit');
    }

    /**
     * 分类更新或者添加
     * @author staitc7 <static7@qq.com>
     */
    public function renew() {
        $Category = $this->app->model('Category');
        $info= $Category->renew();
        if ($info===false) {
            return $this->error($Category->getError());
        }
        $this->app->session->delete('admin_category_menu', 'category_menu');
        return $this->success('操作成功', $this->app->url->build('Category/index'));
    }

    /**
     * 编辑分类
     * @param int $id 分类id
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function edit($id = 0) {
        (int)$id || $this->error('分类id错误');
        $value = $this->app->model('Category')->edit((int)$id);
        return $this->setView([
            'info'=>$value['info'] ?:null,
            'category'=>$value['category'] ?:null,
            'metaTitle' => '编辑分类'
        ],'edit');
    }

    /**
     * 分类删除
     * @param int $id 分类id
     * @author staitc7 <static7@qq.com>
     */
    public function remove($id = 0) {
        (int)$id || $this->error('分类id错误');
        $Category = $this->app->model('Category');
        $category = $Category->where('pid',$id)->where('status','<>', -1)->column('id');
        if ($category) {
            return $this->error('请先删除该分类下的子分类');
        }
        $document = Db::name('Document')->where('category_id',$id)->column('id');
        if ($document) {
            return $this->error('请先删除该分类下的文章（包含回收站）');
        }
        $info = $Category->where('id','in', $id)->delete();
        if ($info !== false) {
            $this->app->session->delete('admin_category_menu', 'category_menu');
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

    /**
     * 移动分类
     * @author staitc7 <static7@qq.com>
     * @param int $id 分类id
     * @return mixed
     */
    public function move($id = 0) {
        (int)$id || $this->error('分类id错误');
        //获取分类
        $list = Db::name('Category')
            ->where('status',1)
            ->where('id','<>', $id)
            ->where('level', '<', 3)
            ->field('id,pid,title')
            ->select();
        array_unshift($list, ['id' => 0, 'title' => '根分类']);
        return $this->setView(['id' => $id, 'list' => $list ?? null]);
    }

    /**
     * 更新移动分类
     * @param int $id 分类id
     * @param int $pid 分类id的新父级id
     * @author staitc7 <static7@qq.com>
     */
    public function moveRenew($id = 0, $pid = 0) {
        (int)$id || $this->error('分类id错误');
        is_numeric((int)$pid) || $this->error('参数错误');
        if ((int)$pid!==0){
            $pid_level = Db::name('Category')->where('id', (int)$pid)->value('level');
            $level=(int)$pid_level+1;
        }else{
            $level=1;
        }
        $status = Db::name('Category')->where('id', $id)->setField(['pid'=>$pid,'level'=>$level]);
        if ($status !== false) {
            $this->app->session->delete('admin_category_menu', 'category_menu');
            return $this->success('移动成功');
        } else {
            return $this->error('移动失败');
        }
    }

    /**
     * 分类图片
     * @author staitc7 <static7@qq.com>
     */
    public function categoryPicture() {
        $data = PictureUpload::upload('categoryPicture');
        return $data!==false ? $this->success('上传成功!', '', $data) : $this->error(PictureUpload::getError());
    }
}