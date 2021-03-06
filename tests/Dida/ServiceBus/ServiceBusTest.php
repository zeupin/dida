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
use Dida\ServiceBus;

class ServiceBusTest extends TestCase
{
    public function test1()
    {
        ServiceBus::set("Request", new Dida\Http\Request);
        ServiceBus::set("Response", new Dida\Http\Response);
        ServiceBus::set("Cookie", new Dida\Http\Cookie);
        ServiceBus::set("Session", new Dida\Http\Session);
    }
}
