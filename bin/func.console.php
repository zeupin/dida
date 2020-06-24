<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

/**
 * 输出一串err类文字
 */
function pr_err($msg)
{
    echo "\033[31m${msg}\033[0m";
}

/**
 * 输出一串notice类文字
 */
function pr_notice($msg)
{
    echo "\033[35m${msg}\033[0m";
}

/**
 * 输出一串info类文字
 */
function pr_info($msg)
{
    echo "\033[36m${msg}\033[0m";
}

/**
 * 输出一串succ类文字
 */
function pr_succ($msg)
{
    echo "\033[32m${msg}\033[0m";
}
