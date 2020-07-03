<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

require __DIR__ . '/../_base.php';
require __DIR__ . '/_test.php';

use PHPUnit\Framework\TestCase;
use Dida\Facade\Request;

class RequestTest extends TestCase
{
    public function testSession()
    {
        $request = new Dida\Http\Request;
        $session = $request->session();
        $this->assertEquals([], $session);
    }

    public function testRequestFacade()
    {
        $session = Request::session();
        $this->assertEquals([], $session);
    }
}
