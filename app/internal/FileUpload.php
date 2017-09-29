<?php
/**
 * Description of FileUpload.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/9/28 16:09
 */

namespace app\internal;

use think\facade\{
    App, Request,Config
};


class FileUpload
{
    protected $error;
    /**
     * 资源媒体类型
     * @var array
     */
    protected $contentType = 'mime';
    // 常用文件格式（包括图片、文件、音视频）
    private $ext = [
        'ext' => 'zip,7z,tar.gz,rar,xls,doc,ppt,xlsx,docx,pptx,rm,rmvb,wmv,avi,mp4,mp3,3gp,mkv,gif,jpg,jpeg,bmp,png,swf,fla,flv'
    ];

    /**
     * 文件上传
     * @param  array  $file_name 要上传的文件名称
     * @param  array  $tmp_config 临时上传配置
     * @param  string $driver 上传驱动名称（后期完善）
     * @return array           文件上传成功后的信息
     * @author staitc7 <static7@qq.com>
     */
    public function upload($file_name = null, $tmp_config = null, $driver = 'local')
    {
        $config  = $tmp_config ?: Config::get('config.file_path');
        $file    = Request::file($file_name);
        if (!$file->check(Config::get('config.file_upload_restrict'))) {
            $this->error=$file->getError(); // 上传失败获取错误信息
            return false;
        }
        if (is_array($file)) {
            $data = $this->arrayProcess($file, $config, $driver);
        } else {
            $data = $this->oneProcess($file, $config, $driver);
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
        $File  = App::model('File');
        $fileInfo = $File->isExist($file->hash('md5'), $file->hash('sha1'));
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
            'mime'  => $info->getInfo('type'),
            'size'  => $info->getInfo('size'),
            'ext'=>$info->getExtension(),
            'create_time' => $info->getATime(),
            'original_name' => $info->getInfo('name'),
            'file_name' => $info->getFilename()
        ];
        $info = $File->renew($data);
        if ($info === false) {
            $this->error = $File->getError();
            return false;
        }
        return $info;
    }

    /**
     * 下载指定文件
     * @param  number  $root 文件存储根目录
     * @param  integer $id 文件ID
     * @param null     $callback 回调
     * @param  string  $args 回调函数参数
     * @return bool false-下载失败，否则输出下载文件
     */
    public function download($root, int $id, $callback = null, $args = null)
    {
        /* 获取下载文件信息 */
        $file = $this::get($id);
        if (empty($file) || empty($file->data)) {
            $this->error = '不存在该文件！';
            return false;
        }
        /* 下载文件 */
        switch ((int)$file->location) {
            case 0:
                //下载本地文件
                $file->rootpath = $root;
                $this->downLocalFile($file->data, $callback, $args);
            case 1:
                //TODO: 下载远程FTP文件
                break;
            default:
                $this->error = '不支持的文件存储类型！';
                return false;
        }

    }

    /**
     * 下载本地文件
     * @param  array    $file 文件信息数组
     * @param  callable $callback 下载回调函数，一般用于增加下载次数
     * @param  string   $args 回调函数参数
     * @return boolean            下载失败返回false
     */
    private function downLocalFile($file, $callback = null, $args = null)
    {
        if (!is_file($file['rootpath'] . $file['savepath'] . $file['savename'])) {
            $this->error = '文件已被删除！';
            return false;
        }
        //调用回调函数新增下载数
//        is_callable($callback) && call_user_func($callback, $args);

        // 执行下载
        header("Content-Description: File Transfer");
        header('Content-type: ' . $this->contentType);
        header('Content-Length:' . $file['size']);
        if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { //for IE
            header('Content-Disposition: attachment; filename="' . rawurlencode($file['name']) . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
        }
        readfile($file['rootpath'] . $file['savepath'] . $file['savename']);
        exit;
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