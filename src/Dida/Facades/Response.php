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
 * Facade methods for Dida\Http\Response
 *
 * @method static void disableCache()
 * @method static void allowCORS()
 * @method static void setHeaders(array $headers)
 * @method static void json(mixed $data, array|string $cacheSetting = 'no-cache')
 * @method static void redirect(string $url, array|null $cacheSetting = null)
 * @method static bool download(string $srcfile, string $name = null, bool $mime = false, array|null $cacheSetting = null)
 */
class Response extends Facade
{
    protected static function setFacadeServiceLink()
    {
        static::$facadeServiceLink = ["Response", Facade::TYPE_SERVICE_BUS, [], false];
    }
}
