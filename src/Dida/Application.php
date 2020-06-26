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
    const VERSION = '20200626';

    /**
     * @var string|null 存放配置文件的目录
     */
    protected static $confdir = null;

    /**
     * 初始化App
     *
     * @param string $confdir 配置文件目录
     *
     * @throws \Exception
     */
    public static function init($confdir)
    {
        // 只允许运行一个Application实例
        if (self::$confdir !== null) {
            $errmsg = 'Only allow a single ' . get_called_class() . ' instance.';
            throw new \Exception($errmsg);
        }

        // 如果配置目录无效,抛异常
        if (!file_exists($confdir) || !is_dir($confdir)) {
            $errmsg = "Invalid configuration directory `$confdir`.";
            throw new \Exception($errmsg);
        }

        // 把路径标准化
        $confdir = realpath($confdir);

        // 形式检查一下confdir里面是否存在几个必须的配置文件.
        // 如果不存在的话,在这里就抛异常.
        $file = $confdir . DIRECTORY_SEPARATOR . 'app.php';
        if (!file_exists($file) || !is_file($file)) {
            $errmsg = "Missing an important configuration file: `$file`.";
            throw new \Exception($errmsg);
        }

        // 保存配置文件目录
        self::$confdir = $confdir;
    }

    /**
     * 运行
     */
    public static function run()
    {
        // 如果还没有初始化,直接抛异常退出
        if (self::$confdir === null) {
            $errmsg = "The `Dida\Application` is not initialized.";
            throw new \Exception($errmsg);
        }

        // 导入内置常量
        include __DIR__ . '/bootstrap/constants.php';

        // 导入内置系统函数
        include __DIR__ . '/bootstrap/functions.php';

        // 载入 app.php
        require self::$confdir . DS . 'app.php';
    }

    /**
     * 返回配置目录的值
     */
    public static function confDir()
    {
        return self::$confdir;
    }
}
