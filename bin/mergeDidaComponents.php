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
require __DIR__ . '/func.dida.merge.php';

// Dida的各个组件
$components = [
    ['dida-config', 'https://github.com/zeupin/dida-config.git'],
    ['dida-container', 'https://github.com/zeupin/dida-container.git'],
    ['dida-log', 'https://github.com/zeupin/dida-log.git'],
    ['dida-console', 'https://gitee.com/zeupin/dida-console.git'],
    ['dida-routing', 'https://gitee.com/zeupin/dida-routing.git'],
];

// 把组件拼起来
foreach ($components as $index => $component) {
    list($name, $giturl) = $component;

    // 横线
    echo sprintf("%s %d %s\n\n", str_repeat('-', 30), $index + 1, str_repeat('-', 30));

    // 合并
    mergeDidaComponentRepo($name, $giturl);
}

// php-cs-fixer
$cmd = "php-cs-fixer fix \"$rootdir\"";
pr_info("$cmd\n");
passthru($cmd);
echo "\n\n";
