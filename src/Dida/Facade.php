<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida;

/**
 * Facade 基类
 */
abstract class Facade
{
    /**
     * 版本号
     */
    const VERSION = '20200625';

    /**
     * @var string 绑定的Facade服务名称
     */
    protected static $didaFacadeServiceName = '';

    /**
     * 绑定Facade的service名称
     */
    abstract protected static function bindFacadeServiceName();

    /**
     * 调用魔术方法
     */
    public static function __callStatic($name, $arguments)
    {
        $callback = [self::$didaFacadeServiceName, $name];

        return call_user_func_array($callback, $arguments);
    }
}
