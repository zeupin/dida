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
 * Performance 性能测试
 */
class Performance
{
    /**
     * 版本号
     */
    const VERSION = '20191202';

    /**
     * 计算两个microtime的间隔
     *
     * @param string $start 用microtime()生成的开始时间
     * @param string $end   用microtime()生成的结束时间
     *
     * @return float 时间间隔(单位s)
     */
    public static function diffMicroTime($start, $end)
    {
        list($sm, $ss) = explode(' ', $start);
        list($em, $es) = explode(' ', $end);

        $sm = floatval($sm);
        $ss = intval($ss);

        $em = floatval($em);
        $es = intval($es);

        return ($es - $ss) + ($em - $sm);
    }

    /**
     * 内存使用情况
     *
     * @return array 单位为字节
     */
    public static function memoryUsage()
    {
        $a = memory_get_usage();
        $b = memory_get_peak_usage();
        $c = memory_get_usage(true);
        $d = memory_get_peak_usage(true);
        return [
            'usage'      => self::readableBytes($a), // 实际使用的内存大小
            'peak_usage' => self::readableBytes($b), // 实际使用的峰值内存大小
            'alloc'      => self::readableBytes($c), // 分配到的内存大小
            'alloc_peak' => self::readableBytes($d), // 分配到的峰值内存大小
        ];
    }

    /**
     * 可读性的字节数
     *
     * 小于1KB，返回 xxx B
     * 1KB-1MB，返回 xxx KB 或 xxx.xx KB
     * 1MB-1GB，返回 xxx MB 或 xxx.xx MB
     * 1GB以上，返回 xxx GB
     *
     * @param int $num 字节数
     *
     * @return string 以B,KB,MB,GB为单位返回
     */
    public static function readableBytes($num)
    {
        // 小于1KB
        if ($num < 1024) {
            return "{$num} B";
        }

        // 1KB 到 1MB
        if ($num < 1048576) {
            $r = $num / 1024;
            if ($num % 1024 == 0) {
                return sprintf('%d KB', $r);
            } else {
                return sprintf('%.2f KB', $r);
            }
        }

        // 1MB 到 1GB
        if ($num < 1073741824) {
            $r = $num / 1048576;
            if ($num % 1048576 == 0) {
                return sprintf('%d MB', $r);
            } else {
                return sprintf('%.2f MB', $r);
            }
        }

        // 更大的，以GB为单位返回
        $r = $num / 1073741824;
        if ($num % 1073741824 == 0) {
            return sprintf('%d GB', $r);
        } else {
            return sprintf('%.2f GB', $r);
        }
    }

    /**
     * 可读性的时间间隔
     *
     * 【1秒以上】
     *     大于100天，返回 xxx.x天
     *      大于10天，返回 xx.xx天
     *   1小时到10天，返回 xxx天xx小时xx分xx秒
     *  2分钟到1小时，返回 xx分xx.x秒
     * 1-120秒整数秒，返回 xx秒
     * 1-120秒小数秒，返回 xx.xxx秒
     *
     * 【1秒以内】
     *  毫秒级，返回 xxx毫秒
     *  微秒级，返回 xxx微秒
     *
     * @param int|float $num 时间间隔，以秒为单位
     *
     * @return string
     */
    public static function readableInterval($num)
    {
        if ($num >= 1) {
            // 大于100天
            if ($num >= 8640000) {
                $days = $num / 86400;
                return sprintf('%.1f天', $days);
            }

            // 大于10天
            if ($num >= 864000) {
                $days = $num / 86400;
                return sprintf('%.2f天', $days);
            }

            // 大于1小时
            if ($num >= 3600) {
                $rest = floor($num);

                $days = ($rest - $rest % 86400) / 86400;
                $rest = $rest - $days * 86400;
                if (!$rest) {
                    return "{$days}天";
                }

                $hours = ($rest - $rest % 3600) / 3600;
                $rest = $rest - $hours * 3600;

                $mins = ($rest - $rest % 60) / 60;
                $secs = $rest - $mins * 60;

                $r = [];
                if ($days > 0) {
                    $r[] = "{$days}天";
                }
                if ($hours > 0 || $r) {
                    $r[] = sprintf('%d小时', $hours);
                }
                if ($mins > 0 || $r) {
                    $r[] = sprintf('%d分', $mins);
                }
                $r[] = sprintf('%d秒', $secs);

                return implode('', $r);
            }

            // 大于2分钟
            if ($num >= 120) {
                $int = round($num, 0);
                $secs = $int % 60;
                $mins = ($int - $secs) / 60;
                if (is_int($num) || ($int == $num)) {
                    if ($secs) {
                        return sprintf('%d分%d秒', $mins, $secs);
                    } else {
                        return "{$mins}分";
                    }
                } else {
                    $secs = $num - $mins * 60; // 带小数的
                    return sprintf('%d分%.1f秒', $mins, $secs);
                }
            }

            // 1-120秒以内，整数秒
            if (is_int($num) || (intval($num) == $num)) {
                return sprintf('%d秒', $num);
            }

            // 1-120秒以内，有小数的秒
            return sprintf('%.3f秒', $num);
        } else {
            // 把小数位放大
            $dec = $num * 1000000;

            // 毫秒级
            if ($dec >= 1000) {
                return sprintf('%.0f毫秒', $dec / 1000);
            }

            // 微秒级
            return sprintf('%.0f微秒', $dec);
        }
    }
}
