<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida;

use \Dida\Config;
use \Dida\ServiceBus;

/**
 * Dida\Application 基类
 */
class Application
{
    /**
     * 版本号
     */
    const VERSION = '20200902';

    /**
     * @var string|null 存放配置文件的目录
     */
    protected $confdir = null;

    /**
     * 初始化App
     *
     * @param string $confdir 配置文件目录
     *
     * @throws \Exception
     */
    public function __construct($confdir)
    {
        // 如果配置目录无效,抛异常
        if (!file_exists($confdir) || !is_dir($confdir)) {
            $errmsg = "Invalid configuration directory `$confdir`.";
            throw new \Exception($errmsg);
        }

        // 把路径标准化
        $confdir = realpath($confdir);

        // 形式检查一下confdir里面是否存在几个必须的配置文件。
        // 如果不存在的话,在这里就抛异常。
        // 1. bootstrap.php 启动配置文件
        // 2. app.php       App配置文件
        $requiredfiles = ["bootstrap.php", "app.php"];
        foreach ($requiredfiles as $filename) {
            $file = $confdir . DS . $filename;
            if (!file_exists($file) || !is_file($file)) {
                $errmsg = "Missing a required configuration file: $file.";
                throw new \Exception($errmsg);
            }
        }

        // 保存配置文件目录
        $this->confdir = $confdir;
    }

    /**
     * 运行
     */
    public function run()
    {
        // 注册App实例
        ServiceBus::set('App', $this);

        // 载入 bootstrap.php
        require $this->confdir . DS . "bootstrap.php";
    }

    /**
     * 结束
     *
     * @todo 在代码中提前结束
     */
    public function end()
    {
    }
}
