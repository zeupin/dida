<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * 官网: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida;

/**
 * ContainerException
 */
class ContainerException extends \Exception
{
    /**
     * 版本号。
     */
    const VERSION = '20191121';

    /**
     * 属性不存在。
     */
    const PROPERTY_NOT_FOUND = 1001;

    /**
     * 服务不存在。
     */
    const SERVICE_NOT_FOUND = 1002;

    /**
     * 已被注册为单例服务，不可生成新的服务实例。
     */
    const SINGLETON_VIOLATE = 1003;

    /**
     * 无效的服务类型。
     */
    const INVALID_SERVICE_TYPE = 1004;
}
