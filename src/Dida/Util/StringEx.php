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
 * StringEx 字符串扩展类
 */
class StringEx
{
    /**
     * Version
     */
    const VERSION = '20200911';

    /**
     * 生成随机字符串
     *
     * @param int    $num 字母个数
     * @param string $set 字符串的可用字符
     */
    public static function randString($num = 32, $set = null)
    {
        if (!$set) {
            $set = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        }
        $len = strlen($set);
        $r = [];
        for ($i = 0; $i < $num; $i++) {
            $r[] = substr($set, mt_rand(0, $len - 1), 1);
        }
        return implode('', $r);
    }

    /**
     * 检查一个字符串是否是以指定的前缀开头
     *
     * @param string       $str      字符串
     * @param string|array $prefixes 可以指定一个或者一组前缀
     *
     * @return boolean 返回验证结果true/false
     */
    public static function matchPrefix($str, $prefixes)
    {
        // 特定的情景
        if ($prefixes === null || $prefixes === '') {
            return true;
        }

        // 如果给出一个字符串前缀
        if (is_string($prefixes)) {
            $len = mb_strlen($prefixes);
            return (mb_substr($str, 0, $len) === $prefixes);
        }

        // 如果给出一组前缀
        if (is_array($prefixes)) {
            foreach ($prefixes as $prefix) {
                if (is_string($prefix)) {
                    $len = mb_strlen($prefix);
                    if (mb_substr($str, 0, $len) === $prefix) {
                        return true;
                    }
                }
            }
            return false;
        }

        // 其它返回false
        return false;
    }

    /**
     * 检查一个字符串是否是以指定的后缀结尾
     *
     * @param string       $str
     * @param string|array $suffixes 可以指定一个或者一组后缀
     *
     * @return boolean 返回验证结果true/false
     */
    public static function matchSuffix($str, $suffixes)
    {
        // 特定的情景
        if ($suffixes === null || $suffixes === '') {
            return true;
        }

        // 如果给出一个字符串后缀
        if (is_string($suffixes)) {
            $len = mb_strlen($suffixes);
            return (mb_substr($str, -$len) === $suffixes);
        }

        // 如果给出一组后缀
        if (is_array($suffixes)) {
            foreach ($suffixes as $suffix) {
                if (is_string($suffix)) {
                    $len = mb_strlen($suffix);
                    if (mb_substr($str, -$len) === $suffix) {
                        return true;
                    }
                }
            }
            return false;
        }

        // 其它返回false
        return false;
    }
}
