<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

/**
 * 生成指定数据库的常用代码片段
 *
 * 用法：
 * php bin/makeDatabaseGist.php --dsn="dsn设置" --username="数据库用户名" --password="密码" --output="输出目录"
 * php bin/makeDatabaseGist.php --conf="数据库配置文件" --output="输出目录"
 *
 * php ..\..\lib\dida\bin\makeDatabaseGist.php --dsn="mysql:host=localhost;port=3306;dbname=crm" --username=root --password= --output=./temp
 */

require dirname(__DIR__) . "/vendor/autoload.php";

// 命令行参数
$args = new Dida\Console\Arguments();

// usage
$usage = <<<TEXT

参数输入不完整，无法继续，请检查。
    
正确用法：
php bin/makeDatabaseGist.php --dsn="dsn设置" --username="数据库用户名" --password="密码" --output="输出目录"
php bin/makeDatabaseGist.php --conf="数据库配置文件" --output="输出目录"

TEXT;

// 检查命令行参数 --output
if (!$args->hasOption("--output")) {
    die($usage);
}

// 获取当前的工作目录
$curdir = getcwd();

// 检查输出目录的有效性
$output = $args->options["--output"];
$outputDir = $curdir . DS . $output;
if (!file_exists($outputDir)) {
    echo <<<TEXT
指定的输出路径 $outputDir 不存在，请检查！
TEXT;
    die();
}

// 如果有 --conf
if ($args->hasOption('--conf')) {
    $confpath = $args->getOption("--conf");
    $path = $curdir . DS . $confpath;
    $conf = require $path;
}

// 检查 --dsn,--username,--password 参数
elseif ($args->hasOptions(['--dsn', '--username', '--password'])) {
    $dsn = $args->options["--dsn"];
    $username = $args->options["--username"];
    $password = $args->options["--password"];

    $conf = [
        'driver'   => "\\Dida\Db\\Driver\\Mysql",
        'dsn'      => $dsn,
        'username' => $username,
        'password' => $password,
        'options'  => [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT         => true
        ],
    ];
}

// 其它情况
else {
    die($usage);
}

$maker = new Dida\Make\Database($conf);
$maker->setOutputDir($outputDir);
$maker->start();
