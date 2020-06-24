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
 * Config
 */
class Config
{
    /**
     * 版本号
     */
    const VERSION = '20191121';

    /**
     * 配置项
     *
     * @var array
     */
    protected static $items = [];

    /**
     * 检查键值是否存在。
     *
     * @param string $key
     */
    public static function has($key)
    {
        return array_key_exists($key, self::$items);
    }

    /**
     * 设置一个配置。
     *
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        self::$items[$key] = $value;
    }

    /**
     * 获取一个配置。
     *
     * @param string $key
     * @param mixed $default
     */
    public static function get($key, $default = null)
    {
        return self::has($key) ? self::$items[$key] : $default;
    }

    /**
     * 删除一个配置。
     *
     * @param string $key
     */
    public static function remove($key)
    {
        unset(self::$items[$key]);
    }

    /**
     * 列出所有配置的键名。
     *
     * @return array
     */
    public static function keys()
    {
        return array_keys(self::$items);
    }

    /**
     * 返回所有的配置项
     */
    public static function all()
    {
        return self::$items;
    }

    /**
     * 对键值进行排序。
     */
    public static function sortKeys()
    {
        ksort(self::$items);
    }

    /**
     * 清空所有配置。
     */
    public static function clear()
    {
        self::$items = [];
    }

    /**
     * 合并设置。
     *
     * @param array $confs 用户设置
     */
    public static function merge(array $confs)
    {
        self::$items = array_merge(self::$items, $confs);
    }

    /**
     * 获取指定的组和该组所有子项的键名
     *
     * @param string $group
     *
     * @return array
     *
     * @example 对于如下配置:
     * [
     *   ...
     *   "db" => xxx,
     *   "db.aaa" => xxx,
     *   "db.bbb" => xxx,
     *   "db.xx.yy" => ...,
     *   ...
     * ]
     * getKeysByGroup("db") 返回 ["db", "db.aaa", "db.bbb", "db.xx.yy"]
     */
    public static function getKeysByGroup($group)
    {
        $return = [];

        // group根项
        if (self::has($group)) {
            $return[] = $group;
        }

        // group子项
        $find = $group . '.';
        $len = mb_strlen($find);
        $keys = array_keys(self::$items);
        sort($keys);
        foreach ($keys as $key) {
            if (strncmp($find, $key, $len) === 0) {
                $return[] = $key;
                $found = true;
            } else {
                if (isset($found)) {
                    break;
                }
            }
        }

        // 返回
        return $return;
    }

    /**
     * 列出指定组名下的所有配置，以及组名项（如果有的话）
     *
     * 例如： Config::getItemsByGroup("db") 将返回
     * [
     *   "db" => xxx,
     *   "db.aaa" => xxx,
     *   "db.bbb" => xxx,
     *   "db.xx.yy" => ...,
     * ]
     *
     * 优化：
     * 1. 假如有99个配置项，里面有db组的2个配置项，那么找到db.的配置项后，
     *    就直接结束循环，不搜列举更多配置项。
     */
    public static function getItemsByGroup($group)
    {
        $return = [];
        $keys = self::getKeysByGroup($group);
        foreach ($keys as $key) {
            $return[$key] = self::$items[$key];
        }
        return $return;
    }

    /**
     * 扩展出一个配置组。
     *
     * @param string $group   组名
     * @param array $items    配置项列表
     *
     * @example
     * $a = [""=>0, "a"=>1, "b"=>2, "c"=>3];
     * Config::groupExpand("db", $a) 的结果是
     * [
     *   "db" => 0,
     *   "db.a" => 1,
     *   "db.b" => 2,
     *   "db.c" => 3,
     * ]
     */
    public static function groupExpand($group, array $items)
    {
        foreach ($items as $key => $value) {
            if ($key === '') {
                self::$items[$group] = $value;
            } else {
                self::$items[$group . '.' . $key] = $value;
            }
        }
    }

    /**
     * 把指定组的所有配置项进行聚拢，是 groupExpand()函数的逆过程。
     *
     * @example   对于如下配置项：
     * [
     *   "db" => 0,
     *   "db.a" => 1,
     *   "db.b" => 2,
     *   "db.c" => 3,
     * ]
     * Config::groupPack("db") 将返回 [""=>0, "a"=>1, "b"=>2, "c"=>3]
     */
    public static function groupPack($group)
    {
        $return = [];

        // 先找到全组的所有配置
        $keys = self::getKeysByGroup($group);

        // 删除键名中的"group."
        $start = mb_strlen($group) + 1;
        foreach ($keys as $key) {
            if ($key === $group) {
                $return[''] = self::$items[$group];
            } else {
                $newkey = mb_substr($key, $start);
                $return[$newkey] = self::$items[$key];
            }
        }

        return $return;
    }

    /**
     * 删除指定组的全部配置项。
     */
    public static function groupClear($group)
    {
        $keys = self::getKeysByGroup($group);
        foreach ($keys as $key) {
            unset(self::$items[$key]);
        }
    }

    /**
     * 从文件中读取配置。
     *
     * @param string $filepath
     * @param string $group
     *
     * @return bool   成功/失败
     */
    public static function load($filepath, $group = null)
    {
        // 如果文件不存在
        if (!file_exists($filepath) || !is_file($filepath)) {
            return false;
        }

        // 读入配置文件
        $require = function () use ($filepath) {
            return require $filepath;
        };
        $items = $require();

        // 读取配置文件失败
        if (empty($items)) {
            return false;
        }

        // 导入读取的配置
        if ($group !== null) {
            $group = $group . '.';
        }
        foreach ($items as $key => $value) {
            self::$items[$group . $key] = $value;
        }
        return true;
    }
}
