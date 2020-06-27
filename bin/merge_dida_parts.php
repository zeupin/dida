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
require __DIR__ . '/func.dida_repo.php';

// Dida的各个零件
$parts = [
    ['dida-application', 'https://gitee.com/zeupin/dida-application.git'],
    ['dida-config', 'https://github.com/zeupin/dida-config.git'],
    ['dida-container', 'https://github.com/zeupin/dida-container.git'],
    ['dida-log', 'https://github.com/zeupin/dida-log.git'],
    ['dida-console', 'https://gitee.com/zeupin/dida-console.git'],
    ['dida-routing', 'https://gitee.com/zeupin/dida-routing.git'],
];

// 把零件拼起来
foreach ($parts as $index => $part) {
    list($name, $giturl) = $part;

    // 横线
    echo sprintf("%s %d %s\n\n", str_repeat('-', 30), $index + 1, str_repeat('-', 30));

    // 合并
    mergeDidaGitRepo($name, $giturl);
}

// php-cs-fixer
$cmd = "php-cs-fixer fix '$rootdir'";
pr_info("$cmd\n\n");
passthru($cmd);
echo "\n\n";
