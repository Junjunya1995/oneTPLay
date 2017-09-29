<?php
/**
 * Description of SystemLang.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-08-08 11:45
 */

namespace app\home\behavior;

use think\facade\{
    Config,Lang
};

class SystemLang
{
    /**
     * 行为入口
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    public function run()
    {
        // 设置允许的语言
        Lang::setAllowLangList(Config::get('config.lang_list'));

    }
}