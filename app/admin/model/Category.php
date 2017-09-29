<?php
/**
 * Description of Category.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-08-03 17:26
 */

namespace app\admin\model;

use think\Model;
use app\admin\traits\Models;
use app\facade\UserInfo;
use think\facade\{
    Request,Cache,App,Hook
};

class Category extends Model
{
    use Models;
    protected $autoWriteTimestamp = true; //自动写入创建和更新的时间戳字段
    protected $auto = ['meta_title'];
    protected $insert = ['status' => 1];
    protected $update = ['title'];

    /**
     * 获取分类树，指定分类则返回指定分类极其子分类，不指定则返回所有分类树
     * @param  integer $id 分类ID
     * @param  string  $field 查询字段
     * @return array          分类树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getTree($id = 0, string $field = '*')
    {
        /* 获取当前分类信息 */
        if ($id) {
            $info = $this->info($id);
            $id   = $info['id'];
        }
        $object = $this::all(function ($query) use ($field) {
            $query->where('status', 'neq', -1)->field($field)->order(['sort' => 'asc']);
        });
        if ($object) {
            $list = list_to_tree($object->toArray(), 'id', 'pid', '_', $id);
            if (isset($info)) { //指定分类则返回当前分类极其子分类
                $info['_'] = $list;
            } else { //否则返回所有分类
                $info = $list;
            }
        }
        return $info;
    }

    /**
     * 更新分类信息
     * @return boolean 更新状态
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function renew() {
        $data = Request::post();
        $validate=App::validate('Category');
        if (isset($data['id'])){
            $validate->scene('edit');
        }
        if (!$validate->check($data)) {
            $this->error=$validate->getError(); // 验证失败 输出错误信息
            return false;
        }
        if ((isset($data[ $this->pk ]) && (int)$data[ $this->pk ])){
            //操作类型 1 更新 2添加
            $object = $this::update($data);
            $type   =  1;
        }else{
            $object = $this::create($data);
            $type   =  2;
        }
        if ($object){
            //执行行为
            Hook::listen('user_behavior', [
                'type' => $type,
                'action' => 'update_category',
                'model' => __CLASS__,
                'record_id' => (int)$data['id'],
                'user_id' => UserInfo::userId()
            ]);
        }
        Cache::clear('sys_category_list', null); //更新分类缓存
        return $object ? $object->toArray() : null;
    }

    /**
     * 编辑分类信息
     * @param int $id 分类id
     * @author staitc7 <static7@qq.com>
     * @return array
     */
    public function edit(int $id = 0) {
        $info = $this->info($id);
        $category = $info ? $this->info($info->pid, 'id,name,title,level') : null;
        return $data = [
            'info' => $info,
            'category' => $category ?? null
        ];
    }


    /**
     * 获取分类详细信息
     * @param int     $id 分类ID或标识
     * @param  string $field 查询字段
     * @return array 分类信息
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function info($id, string $field = '*')
    {
        $object = $this::find(function ($query) use ($id, $field) {
            $query->field($field)->where(is_numeric($id) ? 'id' : 'name', $id);
        });
        return $object ?: null;
    }

    /* =================自动完成===================== */

    protected function setTypeAttr($value) {
        return $value ? implode(',', $value):null;
    }

    protected function setModelAttr($value) {
        return $value ? implode(',', $value):null;
    }

    protected function setNameAttr($value) {
        return $value ? strtolower($value): '';
    }

    protected function setTitleAttr($value) {
        return $value ? htmlspecialchars($value): '';
    }

    protected function setDescriptionAttr($value) {
        return $value ? htmlspecialchars($value): '';
    }

    protected function setMetaTitleAttr($value) {
        return $value ? htmlspecialchars($value): '';
    }
}