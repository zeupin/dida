<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

require __DIR__ . '/../_base.php';

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
        $app = new Application('');
    }

    /**
     * 如果配置目录不含有app.php,应该抛异常
     *
     * @expectedException \Exception
     */
    public function testInit2()
    {
        $app = new Application(__DIR__ . '/conf-null');
    }

    /**
     * 正常
     */
    public function testInit3()
    {
        $app = new Application(__DIR__ . '/conf-test');
    }

    /**
     * 正常运行run()
     */
    public function testRun()
    {
        $app = new Application(__DIR__ . '/conf-test');
        $app->run();
    }
}
