<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * 官网: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */
require realpath(__DIR__ . "/../vendor/autoload.php");
Dida\Autoloader::init();
Dida\Autoloader::addPsr4('Dida', realpath(__DIR__ . '/../src/Dida'));
