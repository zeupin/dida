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
 * Facade methods for Dida\Http\Session
 *
 * @method static void __construct(array $conf = [])
 * @method static void config(array $conf)
 * @method static bool start()
 * @method static bool destory()
 * @method static bool unset()
 * @method static bool has(string $key)
 * @method static mixed|null get(string $key)
 * @method static bool set(string $key, mixed $value)
 * @method static void remove(string $key)
 * @method static getAll()
 */
class Session extends Facade
{
    protected static function setFacadeServiceLink()
    {
        static::$facadeServiceLink = ["Session", Facade::TYPE_SERVICE_BUS, [], false];
    }
}
