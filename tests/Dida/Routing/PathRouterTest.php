<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

require __DIR__ . '/../_base.php';
require __DIR__ . "/Demo.php";

use PHPUnit\Framework\TestCase;
use Dida\Routing\PathRouter;

class PathRouterTest extends TestCase
{
    public function test1()
    {
        $router = new PathRouter;
        $router->loadRoutes(__DIR__ . "/routes.php");

        // 期望为true
        $this->assertTrue($router->match("/login"));

        $router->execute();

        $expect = ['code'=>0, "msg"=>''];
        $this->assertEquals($router->getExecuteResult(), $expect);
    }
}
