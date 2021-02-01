<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Util;

/**
 * ArrayEx
 */
class ArrayEx
{
    /**
     * 版本号
     */
    const VERSION = '20200628';

    /**
     * 如果数组中存在key, 返回对应的value.
     * 否则返回null
     *
     * @param array      $array
     * @param int|string $key
     *
     * @return mixed|null
     */
    public static function getValue(array $array, $key)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        } else {
            return null;
        }
    }

    /**
     * 从指定数组中挑出给定的数据, 组成一个新数组返回。
     *
     * 1. 如果指定的某个key不存在，则返回的数组中，对应的值为null。
     * 2. 如果只是过滤某个数组，应该用allow()或者disallow()方法。
     *
     * @param array $array
     * @param array $keys
     *
     * @return array
     *
     * @example
     *     \Dida\Util\ArrayEx::pick($_POST, ["user","pwd"])
     *     得到结果:
     *     [
     *         "user" => $_POST["user"],
     *         "pwd"  => $_POST["pwd"],
     *     ]
     */
    public static function pick(array $array, array $keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = self::getValue($array, $key);
        }

        // 返回
        return $result;
    }

    /**
     * 从指定数组中剔除指定的keys,返回剩余结果。
     *
     * @param array $array
     * @param array $keys
     *
     * @return array
     *
     * @example
     *     \Dida\Util\ArrayEx::pickExcept($_POST, ["user","pwd"])
     *     得到结果: 返回$_POST剔除了user和pwd后的剩余部分.
     */
    public static function pickExcept(array $array, array $keys)
    {
        // 开始
        $result = $array;

        // 名称
        foreach ($keys as $key) {
            unset($result[$key]);
        }

        // 返回
        return $result;
    }

    /**
     * 将给出的数组，按照第col列，重新生成一个带key的新数组
     *
     * 常用于数据库的操作。数据库先用fetchAll()生成一个结果集数组，然后用这个函数为生成的数组加上特定的keys。
     *
     * @param array      $array 原数组
     * @param int|string $col   指定按照原数组的哪一列作为新数组的key
     *
     * @return array|false 成功返回生成的新数组，失败返回false
     */
    public static function addKeys(array $array, $col)
    {
        $keys = array_column($array, $col);
        return array_combine($keys, $array);
    }

    /**
     * 只允许白名单中的元素，其余元素全部都删除
     *
     * @param array $array
     * @param array $keys  keys的白名单
     *
     * @return array
     */
    public static function allow(array $array, array $keys)
    {
        foreach ($array as $k=>$v) {
            if (!in_array($k, $keys)) {
                unset($array[$k]);
            }
        }
        return $array;
    }

    /**
     * 删除在黑名单中的元素，其它的保留
     *
     * @param array $array
     * @param array $keys  keys的黑名单
     *
     * @return array
     */
    public static function disallow(array $array, array $keys)
    {
        foreach ($array as $k=>$v) {
            if (in_array($k, $keys)) {
                unset($array[$k]);
            }
        }
        return $array;
    }

    /**
     * 在$array中, 检查$keys列表的每个key是否都存在。
     * 返回不存在的keys的清单。
     *
     * @param array $array
     * @param array $keys  要检查的keys
     *
     * @return array 返回缺失keys的列表。
     */
    public static function getMissingKeys(array $array, array $keys)
    {
        $missing = [];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                $missing[] = $key;
            }
        }
        return $missing;
    }
}
