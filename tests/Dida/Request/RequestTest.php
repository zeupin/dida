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
use Dida\Http\Request;

class RequestTest extends TestCase
{
    public function testSession()
    {
        $request = new Request;
        $session = $request->session();
        $this->assertEquals([], $session);
    }
}
