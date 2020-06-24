<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

require __DIR__ . '/func.filesystem.php';

// vendor目录位置
$vendor_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor';

// src目录位置
$src_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src';

// 如果vendor目录不存在, 显示提示信息, 退出
if (!file_exists($vendor_dir) && !is_dir($vendor_dir)) {
    $errmsg = <<<TEXT
The composer `vendor` directory is not found.
The `vendor` path should be `$vendor_dir`.
Your can use `composer update` command to recovery this issue.
TEXT;
    die($errmsg);
}

// 检查vendor里面有没有dida/*组件,没有就直接退出
if (!file_exists("$vendor_dir/dida")) {
    die("Done!\n");
}

// 找到vendor里面的dida/*的各个组件,把各个组件的src目录拷贝到dida/framework相应目录里面
$folders = scandir("$vendor_dir/dida");
foreach ($folders as $folder) {
    if ($folder === '.' || $folder === '..') {
        continue;
    }

    $src = "$vendor_dir/dida/$folder";
    if (file_exists("$vendor_dir/dida/$folder/src/Dida")) {
        if (!copydir("$vendor_dir/dida/$folder/src/Dida", "$src_dir/Dida")) {
            echo "拷贝 $vendor_dir/dida/$folder/src/Dida 失败\n";
        }
    }
}
