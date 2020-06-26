<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

function mergeDidaGitRepo($name, $giturl)
{
    // repo的根目录
    $rootdir = dirname(__DIR__);

    // repo的/src/Dida/目录
    $thisdida = realpath("$rootdir/src/Dida");

    // temp中的临时目录
    $dest = "$rootdir/temp/$name";

    // 如果临时目录已经存在,先删除
    if (file_exists($dest)) {
        pr_info("Remove '$dest' ...\n\n");
        $result = false;
        if (is_dir($dest)) {
            $result = removedir($dest);
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
