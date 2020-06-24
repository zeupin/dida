<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Console;

use \Dida\Console\Arguments;

/**
 * Command 基类
 */
abstract class Command
{
    /**
     * 命令行参数
     *
     * @var Arguments $args
     */
    protected $args;

    /**
     * 构造函数
     */
    public function __construct()
    {
        global $argv;
        $this->args = new Arguments($argv);
    }
}
