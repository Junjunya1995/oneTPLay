<?php

namespace app\home\controller;

use app\home\traits\Home;
use traits\controller\Jump;

class Index
{
    use Home,Jump;

    /**
     * 首页
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function index()
    {
        return $this->setView();
    }

}
