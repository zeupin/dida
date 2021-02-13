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
 * Facade methods for Dida\Console\Console
 *
 * @method static pr(string $msg, string $style = null)
 * @method static prln(string $msg, string $style = null)
 * @method static string ss(string $msg, string $style)
 * @method static err(string $msg)
 * @method static notice(string $msg)
 * @method static info(string $msg)
 */
class Console extends Facade
{
    protected static function setFacadeServiceLink()
    {
        static::$facadeServiceLink = ["Console", Facade::TYPE_SERVICE_BUS, [], false];
    }
}
