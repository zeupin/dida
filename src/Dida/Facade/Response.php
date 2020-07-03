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
 * Facade methods for Dida\Http\Response
 *
 * @method static setNoCache()
 * @method static setAllowCORS()
 * @method static setHeaders(array $headers)
 * @method static bool json(mixed $data, array|string $cacheSetting = 'no-cache')
 * @method static redirect(string $url, array|null $cacheSetting = null)
 * @method static boolean download(string $srcfile, string $name = null, boolean $mime = false, array|null $cacheSetting = null)
 */
class Response extends Facade
{
    protected static function setFacadeServiceLink()
    {
        static::$facadeServiceLink = ["Response", Facade::TYPE_SERVICE_BUS, [], false];
    }
}
