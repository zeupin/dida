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

/*
 * Facade methods for Dida\Http\Cookie
 *
 * @method static __construct(array $conf = [])
 * @method static void config(array $conf)
 * @method static void init()
 * @method static bool set(string $name, string $value, int $expires = 0, bool $secure = false, bool $httponly = false)
 * @method static bool setSafe(string $name, string $value, int $expires = 0, bool $secure = false, bool $httponly = false)
 * @method static string|null get(string $name)
 * @method static string|null getSafe(string $name)
 * @method static array getAll()
 * @method static array getNames()
 * @method static void remove(string $name, $path = null)
 */
class Cookie extends Facade
{
    protected static function setFacadeServiceLink()
    {
        static::$facadeServiceLink = ["Cookie", Facade::TYPE_SERVICE_BUS, [], false];
    }
}
