<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

/**
 * 返回一个字符串格式的高精度时间
 *
 * @return string
 *
 * @example 1599506067.965080
 */
function dida_microtime()
{
    return sprintf("%0.6f", microtime(true));
}
