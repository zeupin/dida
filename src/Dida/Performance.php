<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * 官网: <https://github.com/zeupin/dida>
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
     * @param string $start   microtime()生成的开始时间
     * @param string $end     microtime()生成的结束时间
     *
     * @return float   时间间隔(单位s)
     */
    public static function microtimeDiff($start, $end)
    {
        list($sm, $ss) = explode(" ", $start);
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
     * @return array  单位为字节
     */
    public static function memoryUsage()
    {
        $a = memory_get_usage();
        $b = memory_get_peak_usage();
        $c = memory_get_usage(true);
        $d = memory_get_peak_usage(true);
        return [
            "usage"      => self::readableBytes($a), // 实际使用的内存大小
            "peak_usage" => self::readableBytes($b), // 实际使用的峰值内存大小
            "alloc"      => self::readableBytes($c), // 分配到的内存大小
            "alloc_peak" => self::readableBytes($d), // 分配到的峰值内存大小
        ];
    }


    /**
     * 将一个以字节为单位的内存尺寸格式化成可读字符串
     *
     * @param int $int   内存数
     *
     * @return string 以B,KB,MB,GB为单位返回
     */
    public static function readableBytes($int)
    {
        // 小于1KB
        if ($int < 1024) {
            return "{$int} B";
        }

        // 1KB 到 1MB
        if ($int < 1048576) {
            $m = $int / 1024;
            if ($int % 1024 == 0) {
                return sprintf("%d KB", $m);
            } else {
                return sprintf("%.2f KB", $m);
            }
        }

        // 1MB 到 1GB
        if ($int < 1073741824) {
            $m = $int / 1048576;
            if ($int % 1048576 == 0) {
                return sprintf("%d MB", $m);
            } else {
                return sprintf("%.2f MB", $m);
            }
        }

        // 更大的，以GB为单位返回
        return sprintf("%.2f GB", $int / 1073741824);
    }
}
