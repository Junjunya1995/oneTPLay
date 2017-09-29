<?php
/**
 * Description of Test.php.
 * User: static7 <static7@qq.com>
 * Date: 2017-04-29 21:38
 */

namespace app\home\command;

use think\console\{
    Command,Input,Output
};

class Test extends Command
{
    /**
     * 配置
     * @author staitc7 <static7@qq.com>
     * @return mixed
     */
    protected function configure()
    {
        $this->setName('test')->setDescription('这是一个测试');
    }

    /**
     * 执行
     * @author staitc7 <static7@qq.com>
     * @param Input  $input
     * @param Output $output
     * @return mixed
     */
    protected function execute(Input $input,Output $output)
    {
        $output->writeln('输出');
    }

}