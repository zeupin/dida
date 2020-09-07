<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

/**
 * 批量生成指定类的Facade伪方法
 *
 * 用法:
 * php bin/makeFacades.php
 */

// 导入composer的autoload
require dirname(__DIR__) . "/vendor/autoload.php";

$make = new Dida\Make\Facade();

// 方法:
// $make->buildFacade('参照的原始类类名', 'facade的类名', 'facade的命名空间', '要绑定的服务名', '生成的facade类名.php文件的保存路径');
$make->buildFacade('Dida\Http\Request', 'Request', 'Dida\\Facades', 'Request', dirname(__DIR__) . "/src/Dida/Facades/Request.php");
$make->buildFacade('Dida\Http\Response', 'Response', 'Dida\\Facades', 'Response', dirname(__DIR__) . "/src/Dida/Facades/Response.php");
$make->buildFacade('Dida\Http\Cookie', 'Cookie', 'Dida\\Facades', 'Cookie', dirname(__DIR__) . "/src/Dida/Facades/Cookie.php");
$make->buildFacade('Dida\Http\Session', 'Session', 'Dida\\Facades', 'Session', dirname(__DIR__) . "/src/Dida/Facades/Session.php");
$make->buildFacade('Dida\Console\Console', 'Console', 'Dida\\Facades', 'Console', dirname(__DIR__) . "/src/Dida/Facades/Console.php");
