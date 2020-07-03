<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace \Dida\Facade;

use \Dida\Facade;

/*
 * Facade methods for Dida\Http\Request
 *
 * @method static __construct()
 * @method static array|mixed|null get(string $name = null)
 * @method static array|mixed|null post(string $name = null)
 * @method static array|mixed|null request(string $name = null)
 * @method static array|mixed|null server(string $name = null)
 * @method static array|mixed|null env(string $name = null)
 * @method static array|mixed|null cookie(string $name = null)
 * @method static array|mixed|null session(string $name = null)
 * @method static array|mixed|null files(string $name = null)
 * @method static string|false getMethod()
 * @method static array|false getUrlInfo()
 * @method static string|null|false getPath()
 * @method static string|null|false getQueryString()
 * @method static string|null|false getFragment()
 * @method static string|false getClientIP()
 * @method static getHeaders()
 * @method static getHeader($name)
 * @method static bool isAjax()
 * @method static string|false getReferer()
 * @method static string|false getSchema()
 * @method static string|false getOffsetPath(string $basePath)
 */
class Request extends Facade
{
    protected static function setFacadeServiceLink()
    {
        static::$facadeServiceLink = ["Request", Facade::TYPE_SERVICE_BUS, [], false];
    }
}
