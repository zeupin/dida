<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

/**
 * 定义tests目录路径
 */
if (!defined("TESTS")) {
    define('TESTS', realpath(__DIR__ . '/../..'));
}

/**
 * 载入autoload.php
 */
require dirname(TESTS) . '/vendor/autoload.php';

/**
 * Request --> ServiceBus("Request")
 */
$request = new Dida\Http\Request;
Dida\ServiceBus::set("Request", $request);
