<?php
/**
 * Description of DataBase.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-08-04 17:32
 */

namespace app\admin\controller;

use app\admin\traits\Admin;
use traits\controller\Jump;
use think\Db;
use DataOutput\DataOutput;
class DataBase
{
    use Admin,Jump;

    /**
     * 数据备份首页
     * @author staitc7 <static7@qq.com>
     */

    public function index() {
        $table= Db::connect()->query('SHOW TABLE STATUS');
        $data = array_map('array_change_key_case', $table);
        return $this->setView(['data'=>$data,'metaTitle' => '数据备份']);
    }

    /**
     * 备份数据库
     * @param  String $ids 表名
     * @param  Integer $id 表ID
     * @param  Integer $start 起始行数
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function export($ids = null, $id = null, $start = null) {
        if ($this->app->request->isPost() && !empty($ids) && is_array($ids)) { //初始化
            $path = $this->app->config->get('admin_config.data_backup_path');
            is_dir($path) || mkdir($path, 0755, true);
            //读取备份配置
            $config = [
                'path' => realpath($path) . DIRECTORY_SEPARATOR,
                'part' => $this->app->config->get('admin_config.data_backup_part_size'),
                'compress' => $this->app->config->get('admin_config.data_backup_compress'),
                'level' => $this->app->config->get('admin_config.data_backup_compress_level')
            ];

            //检查是否有正在执行的任务
            $lock = "{$config['path']}backup.lock";
            if (is_file($lock)) {
                return $this->error('检测到有一个备份任务正在执行，请稍后再试！');
            }
            file_put_contents($lock, $this->app->request->time()); //创建锁文件
            //检查备份目录是否可写
            is_writeable($config['path']) || $this->error('备份目录不存在或不可写，请检查后重试！');
            $this->app->session->set('backup_config', $config);

            //生成备份文件信息
            $file = ['name' => date('Ymd-His', $this->app->request->time()), 'part' => 1];
            $this->app->session->set('backup_file', $file);
            //缓存要备份的表
            $this->app->session->set('backup_tables', $ids);

            //创建备份文件
            $Database = new DataOutput($file, $config);
            if (false !== $Database->create()) {
                $tab = ['id' => 0, 'start' => 0];
                return $this->success('初始化成功！', '', ['tables' => $ids, 'tab' => $tab]);
            } else {
                return $this->error('初始化失败，备份文件创建失败！');
            }
        } elseif ($this->app->request->isGet() && is_numeric($id) && is_numeric($start)) { //备份数据
            $tables = $this->app->session->get('backup_tables');
            //备份指定表
            $Database = new DataOutput($this->app->session->get('backup_file'), $this->app->session->get('backup_config'));
            $start = $Database->backup($tables[(int)$id], $start);
            if (false === $start) { //出错
               return $this->error('备份出错！');
            } elseif (0 === $start) { //下一表
                if (isset($tables[++$id])) {
                    $tab = ['id' => $id, 'start' => 0];
                    return $this->success('备份完成！', '', ['tab' => $tab]);
                } else { //备份完成，清空缓存
                    unlink($this->app->session->get('backup_config.path') . 'backup.lock');
                    $this->app->session->set('backup_tables', null);
                    $this->app->session->set('backup_file', null);
                    $this->app->session->set('backup_config', null);
                    return $this->success('备份完成！');
                }
            } else {
                $tab = ['id' => $id, 'start' => $start[0]];
                $rate = floor(100 * ($start[0] / $start[1]));
                return $this->success("正在备份...({$rate}%)", '', ['tab' => $tab]);
            }
        } else { //出错
            return $this->error('参数错误!');
        }
    }

    /**
     * 还原数据库
     * @author staitc7 <static7@qq.com>
     */

    public function import() {
        //列出备份文件列表
        $path_tmp = $this->app->config->get('admin_config.data_backup_path');
        is_dir($path_tmp) || mkdir($path_tmp, 0755, true);
        $path = realpath($path_tmp);
        $flag = \FilesystemIterator::KEY_AS_FILENAME;
        $glob = new \FilesystemIterator($path, $flag);

        $list = [];
        foreach ($glob as $name => $file) {
            if (preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)) {
                $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
                $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                $part = $name[6];
                if (isset($list["{$date} {$time}"])) {
                    $info = $list["{$date} {$time}"];
                    $info['part'] = max($info['part'], $part);
                    $info['size'] = $info['size'] + $file->getSize();
                } else {
                    $info['part'] = $part;
                    $info['size'] = $file->getSize();
                }
                $extension = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                $info['compress'] = ($extension === 'SQL') ? '-' : $extension;
                $info['time'] = strtotime("{$date} {$time}");
                $list["{$date} {$time}"] = $info;
            }
        }
        return $this->setView(['data'=>$list,'metaTitle' => '数据还原']);
    }

    /**
     * 还原数据库
     * @param int $time
     * @param null $part
     * @param null $start
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function revert($time = 0, $part = null, $start = null) {
        if (is_numeric($time) && is_null($part) && is_null($start)) { //初始化
            //获取备份文件信息
            $name = date('Ymd-His', $time) . '-*.sql*';
            $path = realpath($this->app->config->get('admin_config.data_backup_path')) . DIRECTORY_SEPARATOR . $name;
            $files = glob($path);
            $list = [];
            foreach ($files as $name) {
                $basename = basename($name);
                $match = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
                $gz = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
                $list[$match[6]] = [$match[6], $name, $gz];
            }
            ksort($list);
            $last = end($list);//检测文件正确性
            if (count($list) === $last[0]) {
                $this->app->session->set('backup_list', $list); //缓存备份列表
                return $this->success('初始化完成！', '', ['part' => 1, 'start' => 0]);
            } else {
                return $this->error('备份文件可能已经损坏，请检查！');
            }
        } elseif (is_numeric($part) && is_numeric($start)) {
            $list = $this->app->session->get('backup_list');
            $db = new DataOutput($list[$part], [
                'path' => realpath($this->app->config->get('admin_config.data_backup_path')) . DIRECTORY_SEPARATOR,
                'compress' => $list[$part][2]
                ]);
            $start = $db->import($start);
            if (false === $start) {
                return $this->error('还原数据出错！');
            } elseif (0 === $start) { //下一卷
                if (isset($list[++$part])) {
                    $data = ['part' => $part, 'start' => 0];
                    $this->success("正在还原...#{$part}", '', $data);
                } else {
                    $this->app->session->set('backup_list', null);
                   return $this->success('还原完成！');
                }
            } else {
                $data = ['part' => $part, 'start' => $start[0]];
                if ($start[1]) {
                    $rate = floor(100 * ($start[0] / $start[1]));
                    return $this->success("正在还原...#{$part} ({$rate}%)", '', $data);
                } else {
                    $data['gz'] = 1;
                    return $this->success("正在还原...#{$part}", '', $data);
                }
            }
        } else {
            return $this->error('参数错误！');
        }
    }

    /**
     * 删除备份文件
     * @param  Integer $time 备份时间
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function del($time = 0) {
        empty($time) && $this->error('参数错误！');
        $name = date('Ymd-His', $time) . '-*.sql*';
        $path = realpath($this->app->config->get('admin_config.data_backup_path')) . DIRECTORY_SEPARATOR . $name;
        array_map("unlink", glob($path));
        if (count(glob($path))) {
            return $this->error('备份文件删除失败，请检查权限！');
        } else {
            return $this->success('备份文件删除成功！');
        }
    }
}