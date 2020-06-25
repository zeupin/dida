<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Container;

use \ArrayAccess;
use \Closure;
use \Dida\Container\ContainerException;
use \Dida\Container\NotFoundException;
use \Psr\Container\ContainerInterface;
use \ReflectionClass;

/**
 * Container
 *
 * 1. 已实现PSR-11规范。
 * 2. 已实现ArrayAccess接口，可用$container[$id]形式调用。
 * 3. PSR规范(中文版)参见 <https://learnku.com/docs/psr>。
 * 4. PSR-11中，每个容器的条目称为一个 Entry。
 * 5. 每个Entry可为：类名/闭包/服务实例。
 */
class Container implements ArrayAccess, ContainerInterface
{
    /**
     * 版本号
     */
    const VERSION = '20191204';

    /* 类型常量 */
    const CLASSNAME_TYPE = 'classname';     // 类名字符串
    const CLOSURE_TYPE = 'closure';         // 闭包
    const INSTANCE_TYPE = 'instance';       // 服务实例

    /* 登记的所有服务id */
    protected $_keys = [];

    /* 不同种类的服务集合 */
    protected $_classnames = [];  // 类名
    protected $_closures = [];    // 闭包
    protected $_instances = [];   // 已生成的实例
    protected $_singletons = [];  // 单例服务

    /**
     * ArrayAccess: 检查服务是否存在。
     *
     * @param string $id
     */
    public function offsetExists($id)
    {
        return $this->has($id);
    }

    /**
     * ArrayAccess: 获取一个服务。
     *
     * @param string $id
     */
    public function offsetGet($id)
    {
        return $this->get($id);
    }

    /**
     * ArrayAccess: 注册一个服务。
     *
     * @param string                $id
     * @param string|closure|object $service
     */
    public function offsetSet($id, $service)
    {
        return $this->set($id, $service);
    }

    /**
     * ArrayAccess: 删除一个服务。
     *
     * @param string $id
     */
    public function offsetUnset($id)
    {
        return $this->remove($id);
    }

    /**
     * 是否已经注册此id
     *
     * @param string $id
     *
     * @return bool
     */
    public function has($id)
    {
        return array_key_exists($id, $this->_keys);
    }

    /**
     * 注册一个服务
     *
     * @param string                $id
     * @param string|closure|object $service
     *
     * @return Container $this   链式调用
     *
     * @throws ContainerException
     */
    public function set($id, $service)
    {
        // 以最新的为准
        if ($this->has($id)) {
            $this->remove($id);
        }

        // 设置service，失败抛异常
        if (is_string($service)) {
            $this->_keys[$id] = self::CLASSNAME_TYPE;
            $this->_classnames[$id] = $service;
        } elseif (is_object($service)) {
            if ($service instanceof Closure) {
                $this->_keys[$id] = self::CLOSURE_TYPE;
                $this->_closures[$id] = $service;
            } else {
                $this->_keys[$id] = self::INSTANCE_TYPE;
                $this->_instances[$id] = $service;
            }
        } else {
            // 传入的service类型不合法
            throw new ContainerException(null, ContainerException::INVALID_SERVICE_TYPE);
        }

        // 服务注册成功
        return $this;
    }

    /**
     * 注册一个单例服务
     *
     * @param string                $id
     * @param string|closure|object $service
     *
     * @return Container $this   链式调用
     */
    public function setSingleton($id, $service)
    {
        $this->set($id, $service);
        $this->_singletons[$id] = true;
        return $this;
    }

    /**
     * 获取指定id的实体
     *
     * @param string $id
     *
     * @return mixed
     *
     * @throws NotFoundException
     */
    public function get($id)
    {
        // 如果有此服务，默认返回一个共享服务
        if ($this->has($id)) {
            return $this->getShared($id);
        }

        // PSR-11规范: id不存在时，抛出一个实现NotFoundExceptionInterface的异常
        throw new NotFoundException($id);
    }

    /**
     * 返回一个共享的服务实例
     *
     * 如果需要返回新的服务实例，需要用getNew()方法来完成。
     *
     * @param string $id         服务id
     * @param array  $parameters 待传入的参数数组，可选
     *
     * @return mixed
     *
     * @throws NotFoundException
     */
    public function getShared($id, array $parameters = [])
    {
        if (!$this->has($id)) {
            // PSR-11规范: id不存在时，抛出一个实现NotFoundExceptionInterface的异常
            throw new NotFoundException($id);
        }

        $obj = null;

        switch ($this->_keys[$id]) {
            case self::INSTANCE_TYPE:
                return $this->_instances[$id];

            case self::CLOSURE_TYPE:
                //如果服务实例以前已经创建，直接返回创建好的服务实例
                if (isset($this->_instances[$id])) {
                    return $this->_instances[$id];
                }

                // 如果是第一次运行，则创建新服务实例，并保存备用
                $serviceInstance = call_user_func_array($this->_closures[$id], $parameters);
                $this->_instances[$id] = $serviceInstance;
                return $serviceInstance;

            case self::CLASSNAME_TYPE:
                //如果服务实例以前已经创建，直接返回创建好的服务实例
                if (isset($this->_instances[$id])) {
                    return $this->_instances[$id];
                }

                // 如果是第一次运行，则创建新服务实例，并保存备用
                $class = new ReflectionClass($this->_classnames[$id]);
                if (!$class->isInstantiable()) {
                    return null;
                }
                $serviceInstance = new $this->_classnames[$id];
                $this->_instances[$id] = $serviceInstance;
                return $serviceInstance;
        } // switch end
    }

    /**
     * 返回一个新的服务实例
     *
     * @param string $id         服务id
     * @param array  $parameters 待传入的参数数组，可选
     *
     * @return mixed
     *
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function getNew($id, array $parameters = [])
    {
        if (!$this->has($id)) {
            // PSR-11规范: id不存在时，抛出一个实现NotFoundExceptionInterface的异常
            throw new NotFoundException($id);
        }

        if (isset($this->_singletons[$id])) {
            // 已被注册为单例服务，不可生成新的服务实例
            throw new ContainerException(null, ContainerException::SINGLETON_VIOLATE);
        }

        $obj = null;

        switch ($this->_keys[$id]) {
            case self::INSTANCE_TYPE:
                return $this->_instances[$id];

            case self::CLOSURE_TYPE:
                $serviceInstance = call_user_func_array($this->_closures[$id], $parameters);
                return $serviceInstance;

            case self::CLASSNAME_TYPE:
                $class = new ReflectionClass($this->_classnames[$id]);
                if (!$class->isInstantiable()) {
                    return null;
                }
                $serviceInstance = new $this->_classnames[$id];
                return $serviceInstance;
        } // switch
    }

    /**
     * 删除指定的服务
     *
     * @param string $id
     *
     * @return void
     */
    public function remove($id)
    {
        unset($this->_keys[$id], $this->_classnames[$id], $this->_closures[$id], $this->_instances[$id], $this->_singletons[$id]);
    }

    /**
     * 返回所有登记的服务id
     *
     * @return array
     */
    public function keys()
    {
        return $this->_keys;
    }
}
