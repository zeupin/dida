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

/*
 * Facade methods for Dida\Db\Db
 *
 * @method static __construct(array $conf)
 * @method static init()
 */
class Db extends Facade
{
    protected static function setFacadeServiceLink()
    {
        static::$facadeServiceLink = ["Db", Facade::TYPE_SERVICE_BUS, [], false];
    }
}
