<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida;

use \Dida\ServiceBus;

/**
 * Facade 基类
 */
abstract class Facade
{
    /**
     * 版本号
     */
    const VERSION = '20200905';

    /**
     * 服务类型常数
     */
    const TYPE_SERVICE_BUS = "ServiceBus"; // 从ServiceBus中调用
    const TYPE_CLASSNAME = "Classname"; // 静态类可以用这个调用
    const TYPE_INSTANCE = "Instance"; // 具体实例可以用这个调用

    /**
     * @var array FacadeService链接
     *            [token, type, parameters, newInstance]
     *
     * @example
     *      ["Request"      , Facade::TYPE_SERVICE_BUS, [], false]
     *      [$obj           , Facade::TYPE_INSTANCE,    [], false]
     *      ["\Dida\Foo\Bar", Facade::TYPE_CLASSNAME,   [], false]
     */
    protected static $facadeServiceLink = [];

    /**
     * 设置FacadeServiceLink
     *
     * @return void
     *
     * @example
     *    protected static function setFacadeServiceLink()
     *    {
     *       self::$facadeServiceLink = ["Request", Facade::TYPE_SERVICE_BUS, [], false];
     *    }
     */
    abstract protected static function setFacadeServiceLink();

    /**
     * 调用__callStatic魔术方法
     *
     * @param string $method_name
     * @param array  $arguments
     *
     * @return mixed|false 成功,返回执行结果;有错,返回false.
     *
     * @throws \Exception Facade在执行call_user_func_array之前出错
     */
    public static function __callStatic($method_name, $arguments)
    {
        // 设置FacadeService指向链接
        // setFacadeServiceLink()是个抽象函数，在各个Facade的代码里面具体实现
        static::setFacadeServiceLink();

        // 分解数组
        list($token, $type, $parameters, $newInstance) = static::$facadeServiceLink;

        // 如果是ServiceBus类型
        if ($type === Facade::TYPE_SERVICE_BUS) {
            if ($newInstance) {
                $service = ServiceBus::getNew($token, $parameters);
            } else {
                $service = ServiceBus::get($token, $parameters);
            }

            // 如果在实例化的时候有错,抛异常
            if (is_int($service)) {
                throw new \Exception("Getting \"$token\" service from ServiceBus fail.");
            }

            // 构造callback
            $callback = [$service, $method_name];

            // 返回执行结果
            return call_user_func_array($callback, $arguments);
        }

        // 如果是实例类型
        if ($type === Facade::TYPE_INSTANCE) {
            // 已经是实例了,直接用就行
            $callback = [$token, $method_name];

            // 返回执行结果
            return call_user_func_array($callback, $arguments);
        }

        // 如果是静态类类型
        if ($type === Facade::TYPE_CLASSNAME) {
            // callback在类名模式时, 实际执行的是静态方法ClassName::$method_name()
            $callback = [$token, $method_name];

            // 返回执行结果
            return call_user_func_array($callback, $arguments);
        }

        // $type类型非法
        throw new \Exception("Illegal Facade type \"$type\"");
    }
}
