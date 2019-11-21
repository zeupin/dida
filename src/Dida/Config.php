<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * 官网: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida;

use \ArrayAccess;

/**
 * Config
 */
class Config implements ArrayAccess
{
    /**
     * 版本号
     */
    const VERSION = '20191121';

    protected $items = [];


    public function __construct(array $items = [])
    {
        foreach ($items as $key => $value) {
            $this->checkKey($key);
        }

        $this->items = $items;
    }


    /**
     * 实现ArrayAccess。检查键值是否存在。
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }


    /**
     * 实现ArrayAccess。设置一个参数。
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }


    /**
     * 实现ArrayAccess。获取一个参数。
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }


    /**
     * 实现ArrayAccess。删除一个参数。
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }


    /**
     * 检查键值是否存在。
     */
    public function has($key)
    {
        return array_key_exists($key, $this->items);
    }


    /**
     * 设置一个参数。
     */
    public function set($key, $value)
    {
        $this->items[$key] = $value;
    }


    /**
     * 获取一个参数。
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->items[$key] : $default;
    }


    /**
     * 删除一个参数。
     */
    public function remove($key)
    {
        $this->checkLocked();

        unset($this->items[$key]);
    }


    /**
     * 列出所有键名。
     */
    public function keys()
    {
        return array_keys($this->items);
    }


    /**
     * 清空所有参数。
     */
    public function clear()
    {
        $this->items = [];
    }


    /**
     * 列出某个组名下的所有设置项。
     */
    public function getGroupItems($group)
    {
        $find = $group . '.';
        $len = mb_strlen($find);

        // 先调用系统函数，得到key中还有指定组名的所有键值
        $keys = array_keys($this->items, $find, true);

        // 然后再逐个检查，找出以group.开头的那些
        $return = [];
        foreach ($keys as $key) {
            if (strncmp($find, $key, $len) === 0) {
                $return[$key] = $this->items[$key];
            }
        }
        return $return;
    }


    /**
     * 把指定组的所有配置项进行展开。
     */
    public function groupUnpack($group, array $items)
    {
        foreach ($items as $key => $value) {
            $this->items[$group . '.' . $key] = $value;
        }
    }


    /**
     * 把指定组的所有配置项进行聚拢。
     */
    public function groupPack($group)
    {
        $return = [];

        // 先找到全组的所有设置项
        $items = $this->getGroupItems($group);

        // 删除键名中的"group."
        foreach ($items as $key => $value) {
            $newkey = mb_substr($key, mb_strlen($group) + 1);
            $return[$newkey] = $value;
        }

        return $return;
    }


    /**
     * 删除指定组的全部配置项。
     */
    public function groupClear($group)
    {
        $items = $this->getGroupItems($group);

        foreach ($items as $key => $value) {
            unset($this->items[$key]);
        }
    }


    /**
     * 批量设置。
     *
     * @param array $configs 用户设置
     * @param array $defaults 默认设置
     */
    public function batchSet(array $configs, array $defaults = [])
    {
        $new = array_merge($defaults, $configs);
        $this->items = array_merge($this->items, $new);
    }


    /**
     * 对键值进行排序。
     */
    public function sortKeys()
    {
        ksort($this->items);
    }


    /**
     * 从文件中读取配置。
     *
     * @param string $filepath
     * @param string $group
     *
     * @return bool success/fail
     */
    public function load($filepath, $group = '')
    {
        $require = function () use ($filepath) {
            if (file_exists($filepath)) {
                return require($filepath);
            } else {
                return false;
            }
        };
        $items = $require();

        if (empty($items)) {
            return false;
        }

        if ($group === '' || !is_string($group)) {
            $groupname = '';
        } else {
            $groupname = $group . '.';
        }

        foreach ($items as $key => $value) {
            $this->items[$groupname . $key] = $value;
        }
        return true;
    }


    /**
     * 把另外一个配置表合并进来。
     *
     * @param Config $src
     */
    public function merge(Config $src)
    {
        $keys = $src->keys();
        foreach ($keys as $key) {
            $this->items[$key] = $src[$key];
        }
    }
}
