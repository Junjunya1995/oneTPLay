<?php
/**
 * Description of FileUpload.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/9/28 16:06
 */

namespace app\admin\controller;


use app\admin\traits\Admin;
use traits\controller\Jump;
use app\facade\FileUpload;

class Upload
{
    use Admin,Jump;

    /**
     * 上传文件
     * @author 高举中国特色社会主义伟大旗帜
     * @return mixed
     */
    public function upload()
    {
        $data=FileUpload::upload('file');
        return $data!==false ? $this->result($data, 0, '上传成功!') : $this->result('',-1,FileUpload::getError());
    }
    
}