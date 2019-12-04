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
use \Psr\Container\ContainerExceptionInterface;
use \Psr\Container\NotFoundExceptionInterface;

/**
 * ContainerException
 */
class ContainerException extends Exception implements ContainerExceptionInterface, NotFoundExceptionInterface
{
    /**
     * 版本号
     */
    const VERSION = '20191204';

    /**
     * ID不存在
     */
    const ID_NOT_FOUND = 1001;

    /**
     * 属性不存在。
     */
    const PROPERTY_NOT_FOUND = 1002;

    /**
     * 服务不存在。
     */
    const SERVICE_NOT_FOUND = 1003;

    /**
     * 已被注册为单例服务，不可生成新的服务实例。
     */
    const SINGLETON_VIOLATE = 1004;

    /**
     * 无效的服务类型。
     */
    const INVALID_SERVICE_TYPE = 1005;
}
