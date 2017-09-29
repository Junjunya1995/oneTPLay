<?php
/**
 * Description of Picture.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/9/18 23:05
 */

namespace app\admin\model;

use think\facade\{
    Request,Hook
};
use think\Model;
use app\facade\UserInfo;

class Picture extends Model
{
    protected $error;
    protected $autoWriteTimestamp = true; //自动写入创建时间戳字段
    protected $updateTime = false;// 关闭自动写入update_time字段
    protected $insert = ['status' => 1];

    /**
     * 判断图片是否存在
     * @author staitc7 <static7@qq.com>
     * @param string $md5
     * @param string $sha1
     * @return mixed
     */
    public function isExist(string $md5='',string $sha1='')
    {
        if (empty($md5)||empty($sha1)){
            $this->error='参数丢失';
            return false;
        }
        $fileInfo = $this::get(function ($query) use ($md5,$sha1) {
            $query->where('md5',$md5)
                ->where('status',1)
                ->where('sha1',$sha1)
                ->field(['id', 'md5', 'path', 'sha1','file_name','original_name']);
        });
        if($fileInfo){
            //执行行为
            Hook::listen('user_behavior', [
                'type' => 1,
                'action' => 'update_picture',
                'model' => __CLASS__,
                'record_id' => $fileInfo->id,
                'user_id' => UserInfo::userId()
            ]);
            return $fileInfo->toArray();
        }
        return false;
    }

    /**
     * 图片添加或者更新
     * @author staitc7 <static7@qq.com>
     * @param array|null $data
     * @return mixed
     */
    public function renew(?array $data=[])
    {
        if (empty($data)){
            $this->error='参数错误!';
            return false;
        }
        $object=  $this::create($data);
        if($object){
            //执行行为
            Hook::listen('user_behavior', [
                'type' => 2,
                'action' => 'update_picture',
                'model' => __CLASS__,
                'record_id' => $object->id,
                'user_id' => UserInfo::userId()
            ]);
           return $object->visible(['id', 'md5', 'path', 'sha1','original_name'])->toArray();
        }
        return false;
    }

    /**
     * 返回模型的错误信息
     * @access public
     * @return string|array
     */
    public function getError()
    {
        return $this->error;
    }

    /*=============获取器==============*/
    /**
     * 完整地址
     * @author staitc7 <static7@qq.com>
     * @param $value
     * @return mixed
     */
    public function getPathAttr($value)
    {
        return empty($value) ? null: Request::domain().$value;
    }
}