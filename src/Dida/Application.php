<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida;

/**
 * Dida\Application 基类
 */
class Application
{
    /**
     * 版本号
     */
    const VERSION = '20200624';

    /**
     * @var string 存放配置文件的路径
     */
    protected $confdir;

    /**
     * 初始化App实例
     */
    public function __construct($confdir)
    {
        // 配置一些标准化常量
        require __DIR__ . '/constants.php';

        // 如果配置目录无效,抛异常
        if (!file_exists($confdir) || !is_dir($confdir)) {
            throw new \Exception('Invalid configuration directory.');
        }

        // 把路径标准化
        $confdir = realpath($confdir);

        // 形式检查app的几个基本配置文件是否存在.不存在的话,抛异常
        $file = $confdir . DS . 'app.php';
        if (!file_exists($file) || !is_file($file)) {
            $missing = "Missing a required configuration file: '$file'.";
            throw new \Exception($missing);
        }

        // 保存配置文件目录
        $this->confdir = $confdir;
    }

    /**
     * 运行
     */
    public function run()
    {
        // 载入 app.php
        require $this->confdir . DS . 'app.php';
    }
}
