<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

require dirname(__DIR__) . "/vendor/autoload.php";

$make = new Dida\Make\Facade();
$make->facade("Dida\EventBus");
$make->facade("PDO");
