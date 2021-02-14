<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida;

use \ReflectionClass;
use \Closure;

/**
 * 服务总线
 */
class ServiceBus
{
    /**
     * 版本号
     */
    const VERSION = '20200627';

    /* 类型常量 */
    const CLASSNAME_TYPE = 'CLASSNAME';     // 类名字符串类型
    const CLOSURE_TYPE = 'CLOSURE';         // 闭包类型
    const INSTANCE_TYPE = 'INSTANCE';       // 服务实例类型

    /**
     * 错误常量
     */
    const ERROR = 2000;
    const ERROR_INVALID_SERVICE_TYPE = 2001;
    const ERROR_SERVICE_NOT_FOUND = 2002;
    const ERROR_SERVICE_SINGLETON_VIOLET = 2003;
    const ERROR_SERVICE_NOT_ = 2004;

    /* 登记的所有服务名 */
    protected static $names = [];

    /* 不同种类的服务集合 */
    protected static $classnames = [];  // 类名
    protected static $closures = [];    // 闭包
    protected static $instances = [];   // 已生成的实例
    protected static $singletons = [];  // 单例服务

    /**
     * 服务名是否存在
     *
     * @param string $name 服务名
     *
     * @return bool
     */
    public static function has($name)
    {
        return array_key_exists($name, self::$names);
    }

    /**
     * 注册一个服务
     *
     * @param string                 $name
     * @param string|\Closure|object $service
     *
     * @return int 成功返回0,失败返回错误码
     *
     * @example
     * ServiceBus::set('Request', \Dida\Http\Request::class);
     * @example
     * ServiceBus::set("Db", function () use ($foo, $bar) {
     *     $conf = require __DIR__ . "/conf/mysql.php";
     *     $db = new \Dida\Db\Db($conf);
     *     return $db;
     * });
     * @example
     * ServiceBus::set("App", $app);
     */
    public static function set($name, $service)
    {
        // 以最新的为准
        if (self::has($name)) {
            self::remove($name);
        }

        // 设置service，失败抛异常
        if (is_string($service)) {
            self::$names[$name] = ServiceBus::CLASSNAME_TYPE;
            self::$classnames[$name] = $service;
        } elseif (is_object($service)) {
            if ($service instanceof \Closure) {
                self::$names[$name] = ServiceBus::CLOSURE_TYPE;
                self::$closures[$name] = $service;
            } else {
                self::$names[$name] = ServiceBus::INSTANCE_TYPE;
                self::$instances[$name] = $service;
            }
        } else {
            // 传入的service类型不合法
            return ServiceBus::ERROR_INVALID_SERVICE_TYPE;
        }

        // 注册成功
        return 0;
    }

    /**
     * 注册一个单例服务
     *
     * @param string                $name
     * @param string|closure|object $service
     *
     * @return int 成功返回0,失败返回错误码
     */
    public static function setSingleton($name, $service)
    {
        // 先进行一般性设置
        $result = self::set($name, $service);

        // 如有错,返回错误码
        if ($result) {
            return $result;
        }

        // 处理为singleton
        self::$singletons[$name] = true;

        // 注册成功
        return 0;
    }

    /**
     * 返回一个共享的服务实例
     *
     * 1. 如果服务不存在,抛NotFoundException异常.
     * 2. 如果需要返回新的服务实例，需要用getNew()方法来完成。
     * 3. 参数$parameters仅仅适用于Closure或者Classname模式,且仅在初次时有效.
     *    一旦Closure或者Classname生成了实例,再次调用时,就会直接调用生成好的实例.
     *
     * @param string $name       服务名
     * @param array  $parameters 待传入的参数数组,可选
     *
     * @return object|string|int 成功返回服务实例或静态类名,失败返回错误码
     */
    public static function get($name, array $parameters = [])
    {
        // 如果服务不存在,返回错误码
        if (!self::has($name)) {
            return ServiceBus::ERROR_SERVICE_NOT_FOUND;
        }

        // 根据不同的服务类型,生成对应的服务实例
        switch (self::$names[$name]) {
            case self::INSTANCE_TYPE:
                return self::$instances[$name];

            case self::CLOSURE_TYPE:
                //如果服务实例以前已经创建，直接返回创建好的服务实例
                if (isset(self::$instances[$name])) {
                    return self::$instances[$name];
                }

                // 如果是第一次运行，则创建新服务实例，并保存备用
                $serviceInstance = call_user_func_array(self::$closures[$name], $parameters);
                self::$instances[$name] = $serviceInstance;

                // 返回生成的服务实例
                return $serviceInstance;

            case self::CLASSNAME_TYPE:
                //如果服务实例以前已经创建，直接返回创建好的服务实例
                if (isset(self::$instances[$name])) {
                    return self::$instances[$name];
                }

                // 如果是第一次运行，则创建新服务实例，并保存备用
                $class = new ReflectionClass(self::$classnames[$name]);

                // 如果类可实例化, 返回生成的服务实例
                // 如果类不可被实例化, 返回类名
                if ($class->isInstantiable()) {
                    // 生成的服务实例
                    $serviceInstance = new self::$classnames[$name]($parameters);
                    self::$instances[$name] = $serviceInstance;

                    // 返回
                    return $serviceInstance;
                } else {
                    // 返回类名
                    return self::$classnames[$name];
                }
        }
    }

    /**
     * 返回一个新的服务实例
     *
     * @param string $name       服务名
     * @param array  $parameters 待传入的参数数组，可选
     *
     * @return object|string|int 成功返回服务实例或静态类名,失败返回错误码
     */
    public static function getNew($name, array $parameters = [])
    {
        // 如果服务不存在,返回错误码
        if (!self::has($name)) {
            return ServiceBus::ERROR_SERVICE_NOT_FOUND;
        }

        // 已被注册为单例服务，不可生成新的服务实例
        if (isset(self::$singletons[$name])) {
            throw new \Exception("'$name' is a singleton type.");
        }

        // 生成新的服务实例
        switch (self::$names[$name]) {
            case self::INSTANCE_TYPE:
                return self::$instances[$name];

            case self::CLOSURE_TYPE:
                $serviceInstance = call_user_func_array(self::$closures[$name], $parameters);
                return $serviceInstance;

            case self::CLASSNAME_TYPE:
                $class = new ReflectionClass(self::$classnames[$name]);

                // 如果类可被实例化
                if ($class->isInstantiable()) {
                    // 返回一个新实例
                    $serviceInstance = new self::$classnames[$name]($parameters);
                    return $serviceInstance;
                } else {
                    // 返回类名
                    return self::$classnames[$name];
                }
        }
    }

    /**
     * 删除指定的服务
     *
     * @param string $name
     *
     * @return void
     */
    public static function remove($name)
    {
        unset(
            self::$names[$name],
            self::$classnames[$name],
            self::$closures[$name],
            self::$instances[$name],
            self::$singletons[$name]
        );
    }

    /**
     * 返回所有登记的服务名
     *
     * @return array
     */
    public static function names()
    {
        return self::$names;
    }
}
