<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

use \Dida\Config;
use \Dida\ServiceBus;
use \App\Router; // 如果Router的namespace不是App，记得在这里修改！

/*
 * ------------------------------------------------------------
 * App
 * ------------------------------------------------------------
 */
Config::load(__DIR__ . "/app.php");
date_default_timezone_set(Config::get("app.timezone"));

/*
 * ------------------------------------------------------------
 * 注册服务
 * ------------------------------------------------------------
 */
ServiceBus::set('Request', Dida\Http\Request::class);
ServiceBus::set('Response', Dida\Http\Response::class);
ServiceBus::set('Session', Dida\Http\Session::class);
ServiceBus::set('Cookie', Dida\Http\Cookie::class);

/*
 * ------------------------------------------------------------
 * Router
 * ------------------------------------------------------------
 */
$router = new Router;
ServiceBus::set('Router', $router);

$routepath = Dida\Facade\Request::getOffsetPath(DIDA_APP_URL);
if (!$router->match($routepath)) {
    die("404 File Not Found");
}
$router->execute();
