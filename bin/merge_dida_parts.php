<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

/**
 * 把Dida的各个零部件的最新版本合并到本repo
 */

require __DIR__ . '/func.console.php';
require __DIR__ . '/func.filesystem.php';

// Dida的各个零件
$parts = [
    ['dida-application', 'https://gitee.com/zeupin/dida-application.git'],
    ['dida-config', 'https://github.com/zeupin/dida-config.git'],
    ['dida-container', 'https://github.com/zeupin/dida-container.git'],
    ['dida-log', 'https://github.com/zeupin/dida-log.git'],
    ['dida-console', 'https://gitee.com/zeupin/dida-console.git'],
    ['dida-routing', 'https://gitee.com/zeupin/dida-routing.git'],
];

// repo的根目录
$rootdir = dirname(__DIR__);

// repo的/src/Dida/目录
$thisdida = realpath("$rootdir/src/Dida");

// 把零件拼起来
foreach ($parts as $index => $part) {
    list($name, $giturl) = $part;

    // temp中的临时目录
    $dest = "$rootdir/temp/$name";

    // 横线
    echo sprintf("%s %d %s\n\n", str_repeat('-', 30), $index + 1, str_repeat('-', 30));

    // 如果临时目录已经存在,先删除
    if (file_exists($dest)) {
        pr_info("Remove '$dest' ...\n\n");
        $result = false;
        if (is_dir($dest)) {
            $result = removedir($dest) ;
        } else {
            $result = unlink($dest);
        }
        if ($result) {
            echo 'Done.';
        } else {
            pr_err('Fail.');
        }
        echo "\n\n";
    }

    // 用 `git clone` 命令把远程repo的最新版本下载到临时目录
    $cmd = "git clone --depth=1 '$giturl' '$dest'";
    pr_info("$cmd\n\n");
    passthru($cmd);
    echo "\n";

    // 检查 `git clone` 是否成功
    $srcdida = "$dest/src/Dida";
    if (!file_exists($srcdida) || !is_dir($srcdida)) {
        pr_err("'$srcdida' not found!\n");
        echo "Please check.\n\n";
        die();
    }

    // 拷贝组件的src目录到本repo
    $cmd = 'Copying files ... ';
    pr_info("$cmd\n\n");
    echo copydir($srcdida, $thisdida) ? 'Done.' : 'Fail.';
    echo "\n\n";
}

$cmd = "php-cs-fixer fix '$rootdir'";
pr_info("$cmd\n\n");
passthru($cmd);
echo "\n\n";
