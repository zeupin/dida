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
    $DS = DIRECTORY_SEPARATOR;

    // repo的根目录
    $this_root = dirname(__DIR__);

    // repo的src和tests目录
    $this_src = realpath("$this_root/src");
    $this_tests = realpath("$this_root/tests");

    // temp的根目录
    $temp_root = "$this_root${DS}temp${DS}$name";

    // 如果temp的根目录已经存在,先删除
    if (file_exists($temp_root)) {
        pr_info("Remove \"$temp_root\" ... ");
        $result = false;
        if (is_dir($temp_root)) {
            $result = removedir($temp_root);
        } else {
            $result = unlink($temp_root);
        }
        if ($result) {
            pr_succ("Done.\n");
        } else {
            pr_err("Fail.\n");
            die();
        }
    }

    // 用 `git clone` 命令把远程repo的最新版本下载到临时目录
    $cmd = "git clone --depth=1 \"$giturl\" \"$temp_root\"";
    pr_info("$cmd\n");
    $ret = 0;
    passthru($cmd, $ret);

    // temp的src和tests目录
    $temp_src = $temp_root . $DS . "src";
    $temp_tests = $temp_root . $DS . "tests";
    if (file_exists($temp_src) && is_dir($temp_src)) {
        $temp_src = realpath($temp_src);
    } else {
        $temp_src = null;
    }
    if (file_exists($temp_tests) && is_dir($temp_tests)) {
        $temp_tests = realpath($temp_tests);
    } else {
        $temp_tests = null;
    }

    // 检查 `git clone` 是否成功
    if ($temp_src) {
        pr_succ("Success.\n");
    } else {
        $errmsg = "Error! git clone fail!\n";
        pr_err($errmsg);
        die();
    }

    // 拷贝src目录
    $cmd = "Copying '$temp_src' ... ";
    pr_info("$cmd");
    copydir($temp_src, $this_src) ? pr_succ("Done.\n") : pr_err("Fail.\n");

    // 拷贝tests目录(如果有的话)
    if ($temp_tests) {
        $cmd = "Copying \"$temp_tests\" files ... ";
        pr_info("$cmd");
        copydir($temp_temp, $this_temp) ? pr_succ("Done.\n") : pr_err("Fail.\n");
    }

    // 全部完成
    echo "\n";
}
