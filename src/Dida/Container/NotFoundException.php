<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * 官网: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */
namespace Dida\Container;

use \Exception;
use \Psr\Container\NotFoundExceptionInterface;

/**
 * NotFoundException
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    /**
     * 版本号
     */
    const VERSION = '20191204';
}
