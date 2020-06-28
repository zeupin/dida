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
     * 从指定数组中挑出给定的数据, 组成一个新数组返回
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
}
