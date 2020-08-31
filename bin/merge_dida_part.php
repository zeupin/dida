
<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

/**
 * 把Dida的各个组件的最新版本合并到本repo
 */

require __DIR__ . '/func.console.php';
require __DIR__ . '/func.filesystem.php';
require __DIR__ . '/func.dida_repo.php';

// repo的根目录
$rootdir = dirname(__DIR__);

// repo的/src/Dida/目录
$thisdida = realpath("$rootdir/src/Dida");

// 参数1=giturl
if ($argc < 2) {
    exit;
} else {
    $giturl = $argv[1];
}

// 参数2=名字(选填。如果没有，默认设为 merge-dida-component)
if ($argc < 3) {
    $name = 'merge-dida-component';
} else {
    $name = $argv[2];
}

// 导入
mergeDidaGitRepo($name, $giturl);

// php-cs-fixer
$cmd = "php-cs-fixer fix \"$rootdir\"";
pr_info("$cmd\n");
passthru($cmd);
echo "\n";
