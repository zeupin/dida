<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

/**
 * 设置为生产环境
 */
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);

/**
 * 是否开启调试
 */
defined('DIDA_DEBUG') || define('DIDA_DEBUG', false);

/**
 * 是否显示调试的堆栈信息
 */
defined('DIDA_DEBUG_BACKTRACE') || define('DIDA_DEBUG_BACKTRACE', false);
