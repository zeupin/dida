<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Facades;

use \Dida\Facade;

class Router extends Facade
{
    protected static function setFacadeServiceLink()
    {
        static::$facadeServiceLink = ["Router", Facade::TYPE_SERVICE_BUS, [], false];
    }
}
