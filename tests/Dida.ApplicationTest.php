<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

require __DIR__ . '/_base.php';

use PHPUnit\Framework\TestCase;
use Dida\Application;

class ApplicationTest extends TestCase
{
    /**
     * 如果配置目录不存在,应该抛异常
     *
     * @expectedException \Exception
     */
    public function testInit1()
    {
        Application::init('');
    }

    /**
     * 如果配置目录不含有app.php,应该抛异常
     *
     * @expectedException \Exception
     */
    public function testInit2()
    {
        Application::init(TESTS . '/conf-null');
    }

    /**
     * 正常
     */
    public function testInit3()
    {
        Application::init(TESTS . '/conf-test');
    }

    /**
     * 不允许多次初始化
     *
     * @expectedException \Exception
     */
    public function testInit4()
    {
        Application::init(TESTS . '/conf-test');
        Application::init(TESTS . '/conf-test');
    }

    /**
     * 如果还没有进行init()就执行run(),抛异常
     *
     * @expectedException \Exception
     */
    public function testRun1()
    {
        MyApp::setConfDir(null);

        // confdir现在应该为null
        $this->assertEquals(null, Application::confDir());

        // run()
        Application::run();
    }
}

/**
 * 辅助类
 */
class MyApp extends Application
{
    /**
     * 强行修改$confdir的值
     */
    public static function setConfDir($confdir)
    {
        parent::$confdir = $confdir;
    }
}
