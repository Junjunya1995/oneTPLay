<?php
/**
 * Description of template.php.
 * User: static7 <static7@qq.com>
 * Date: 2017/8/21 17:36
 */
use think\facade\{
    Env
};
return [
    // 模板引擎类型 支持 php think 支持扩展
    'type'         => 'Think',
    // 模板路径
    'view_path'    =>'',
    // 模板后缀
    'view_suffix'  => 'html',
    // 模板文件名分隔符
    'view_depr'    => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'    => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'      => '}',
    // 标签库标签开始标记
    'taglib_begin' => '{',
    // 标签库标签结束标记
    'taglib_end'   => '}',
    //全局的视图根目录
    'view_base'=>Env::get('root_path').'./template/',
];