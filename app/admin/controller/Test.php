<?php
namespace app\admin\controller;

use app\admin\traits\Admin;
use traits\controller\Jump;

class Test
{
    //引入容器
    use Admin,Jump;

    /**
     * 打印系统变量
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function config()
    {
//        $value=$this->app->config->pull('admin_config');
        $values=$this->app->config->get('config.picture_upload_restrict.ext');
        dump($values);
    }

    /**
     * 上传
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function upload()
    {
        return $this->setView();
    }
    /**
     * 上传测试
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function up(){
        // 获取表单上传文件
        $files = request()->file('image');

            // 移动到框架应用根目录/uploads/ 目录下
            $info = $files->move(getcwd() .$this->app->config->get('config.picture_path'));
            if($info){
                dump($info->getSaveName());
            }else{
                // 上传失败获取错误信息
                echo $files->getError();
            }

    }
}