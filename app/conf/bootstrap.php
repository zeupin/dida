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
ServiceBus::set('Request', new Dida\Http\Request);
ServiceBus::set('Response', new Dida\Http\Response);
ServiceBus::set('Session', new Dida\Http\Session);
ServiceBus::set('Cookie', new Dida\Http\Cookie);

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
