<?php
/**
 * Description of Article.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/8/3 21:49
 */

namespace app\admin\controller;


use app\admin\traits\Admin;
use traits\controller\Jump;

class Article
{
    use Admin,Jump;

    /**
     * 我的文章
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function myDocument()
    {
        return $this->setView([],'my_document');
    }
}