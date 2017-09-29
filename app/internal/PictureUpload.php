<?php
/**
 * Description of PictureUpload.phpphp.
 * User: static7 <static7@qq.com>
 * Date: 2017/9/18 22:55
 */

namespace app\internal;

use think\facade\{
    App, Request,Config
};

class PictureUpload
{
    protected $error;

    /**
     * 文件上传
     * @param  array  $file_name 要上传的文件名称
     * @param  array  $uploadPath 临时上传路径配置
     * @param  string $driver 上传名称（后期完善）
     * @return array           文件上传成功后的信息
     * @author staitc7 <static7@qq.com>
     */
    public function upload($file_name = null, $uploadPath = null, $driver = 'local')
    {
        $uploadPath = empty($picturePath) ? Config::get('config.picture_path') : $uploadPath;
        $file       = Request::file($file_name);
        if (is_array($file)) {
            foreach ($file as $k => $v) {
                if (!$v->check(Config::get('config.picture_upload_restrict'))) {
                    $this->error = $file->getError(); // 上传失败获取错误信息
                    return false;
                }
            }
            $data = $this->arrayProcess($file, $uploadPath, $driver);
        } else {
            if (!$file->check(Config::get('config.picture_upload_restrict'))) {
                $this->error = $file->getError(); // 上传失败获取错误信息
                return false;
            }
            $data = $this->oneProcess($file, $uploadPath, $driver);
        }
        return $data;
    }

    /**
     * 单个文件处理
     * @param array  $file 处理的数据
     * @param  array $uploadPath 上传配置
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    private function oneProcess($file, $uploadPath)
    {
        //检测文件 是否存在
        $Picture  = App::model('Picture');
        $fileInfo = $Picture->isExist($file->hash('md5'), $file->hash('sha1'));
        if ($fileInfo !== false) {
            return $fileInfo;
        }
        //移动文件
        $info = $file->rule('uniqid')->move(getcwd() . $uploadPath);
        if (!$info) {
            $this->error = $file->getError(); // 上传失败获取错误信息
            return false;
        }
        $data = [
            'md5' => $file->hash('md5'),
            'sha1' => $file->hash('sha1'),
            'path' => $uploadPath . $info->getFilename(),
            'create_time' => $info->getATime(),
            'original_name' => $info->getInfo('name'),
            'file_name' => $info->getFilename()
        ];
        $info = $Picture->renew($data);
        if ($info === false) {
            $this->error = $Picture->getError();
            return false;
        }
        return $info;
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
}