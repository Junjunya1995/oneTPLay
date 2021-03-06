<?php

// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Storage;

// 本地文件写入存储类
class Storage {

    /**
     * @var object 对象实例
     */
    protected static $instance;
    private $contents = [];

    /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return \think\Request
     */
    public static function instance($options = []) {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 文件内容读取
     * @access public
     * @param string $filename  文件名
     * @return string     
     */
    public function read($filename, $type = '') {
        return $this->get($filename, 'content', $type);
    }

    /**
     * 文件写入
     * @access public
     * @param string $filename 文件名
     * @param string $content 文件内容
     * @param string $type
     * @return bool
     * @throws \think\Exception
     */
    public function put($filename, $content, $type = '') {
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (false === file_put_contents($filename, $content)) {
            throw new \think\Exception('存储器写入错误:' . $filename);
        } else {
            $this->contents[$filename] = $content;
            return true;
        }
    }

    /**
     * 文件追加写入
     * @access public
     * @param string $filename 文件名
     * @param string $content 追加的文件内容
     * @param string $type
     * @return bool
     */
    public function append($filename, $content, $type = '') {
        if (is_file($filename)) {
            $content = $this->read($filename, $type) . $content;
        }
        return $this->put($filename, $content, $type);
    }

    /**
     * 加载文件
     * @access public
     * @param $_filename
     * @param array $vars 传入变量
     * @internal param string $filename 文件名
     */
    public function load($_filename, $vars = null) {
        if (!is_null($vars)) {
            extract($vars, EXTR_OVERWRITE);
        }
        include $_filename;
    }

    /**
     * 文件是否存在
     * @access public
     * @param string $filename 文件名
     * @param string $type
     * @return bool
     */
    public function has($filename, $type = '') {
        return is_file($filename);
    }

    /**
     * 文件删除
     * @access public
     * @param string $filename 文件名
     * @param string $type
     * @return bool
     */
    public function unlink($filename, $type = '') {
        unset($this->contents[$filename]);
        return is_file($filename) ? unlink($filename) : false;
    }

    /**
     * 读取文件信息
     * @access public
     * @param string $filename 文件名
     * @param string $name 信息名 mtime或者content
     * @param string $type
     * @return bool
     */
    public function get($filename, $name, $type = '') {
        if (!isset($this->contents[$filename])) {
            if (!is_file($filename)) {
                return false;
            }
            $this->contents[$filename] = file_get_contents($filename);
        }
        $content = $this->contents[$filename];
        $info = [
            'mtime' => filemtime($filename),
            'content' => $content
        ];
        return $info[$name];
    }

}
