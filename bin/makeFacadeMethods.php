<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

/**
 * 生成指定类的Facade伪方法
 *
 * 用法: php bin/makeFacade.php 要解析的类名
 *
 * @example
 * php bin/makeFacadeMethods.php \PDO
 * php bin/makeFacadeMethods.php \Dida\Http\Request
 * php bin/makeFacadeMethods.php Dida\Http\Response
 */

// 导入composer的autoload
require dirname(__DIR__) . "/vendor/autoload.php";

// 命令行参数
$args = new Dida\Console\Arguments();

// 生成
if (isset($args->parameters[1])) {
    $make = new Dida\Make\Facade();
    echo "\n";
    echo $make->facade($args->parameters[1]);
    echo "\n";
} else {
}
