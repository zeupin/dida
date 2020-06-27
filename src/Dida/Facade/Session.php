<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Facade;

use \Dida\Facade;

class Session extends Facade
{
    protected static function setFacadeServiceLink()
    {
        self::$facadeServiceLink = ["Session", Facade::TYPE_SERVICE_BUS, [], false];
    }
}
