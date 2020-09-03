<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

/**
 * 设置为测试环境
 */
error_reporting(-1);
ini_set('display_errors', '1');

/**
 * 是否开启调试
 */
defined('DIDA_DEBUG') || define('DIDA_DEBUG', true);

/**
 * 是否显示调试的堆栈信息
 */
defined('DIDA_DEBUG_BACKTRACE') || define('DIDA_DEBUG_BACKTRACE', true);
