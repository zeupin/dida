<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

use Dida\Config;
use App\Router;

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
Dida\ServiceBus::set('App', $app);
Dida\ServiceBus::set('Request', new Dida\Http\Request);
Dida\ServiceBus::set('Response', new Dida\Http\Response);
Dida\ServiceBus::set('Session', new Dida\Http\Session);
Dida\ServiceBus::set('Cookie', new Dida\Http\Cookie);

/*
 * ------------------------------------------------------------
 * Router
 * ------------------------------------------------------------
 */
$router = new Router;
Dida\ServiceBus::set('Router', $router);

$routepath = Dida\Facade\Request::getOffsetPath(DIDA_APP_URL);
if (!$router->match($routepath)) {
    die("404 File Not Found");
}
Router::execute();
