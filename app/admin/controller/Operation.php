<?php
/**
 * Description of Operation.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/8/3 21:29
 */

namespace app\admin\controller;


use app\admin\traits\Admin;
use traits\controller\Jump;

class Operation
{
    use Admin, Jump;

    /**
     * 运营统计
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function index()
    {
        return $this->setView(['metaTitle' => '运营统计']);
    }
}