<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

/**
 * 拷贝目录.
 *
 * 把 src/ 目录的所有内容拷贝到的 dest/ 目录.
 *
 * @param string $src  源目录
 * @param string $dest 目标目录
 *
 * @return bool 成功返回true, 失败返回false
 */
function copydir($src, $dest)
{
    // 如果src不存在,返回false
    if (!file_exists($src) || !is_dir($src)) {
        return false;
    }

    // 如果dest目录不存在,先创建
    if (!file_exists($dest)) {
        // 按照src的permission来创建dest的permission
        $src_perms = fileperms($src);

        // 创建dest目录
        $result = mkdir($dest, $src_perms, true);

        // 如果创建dest失败,返回false
        if (!$result) {
            return false;
        }
    }

    // 递归拷贝src下的每个文件和子目录到dest
    $files = scandir($src);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $s = "$src/$file";
        $d = "$dest/$file";

        if (is_dir($s)) {
            // 拷贝子目录
            if (!copydir($s, $d)) {
                return false;
            }
        } else {
            // 拷贝文件
            if (!copy($s, $d)) {
                return false;
            }

            // 保留原文件的mtime和atime
            $mtime = filemtime($s);
            $atime = fileatime($s);
            touch($d, $mtime, $atime);
        }
    }

    // 成功
    return true;
}

/**
 * 删除指定目录
 *
 * @param string $dir
 *
 * @return bool 成功返回true,失败返回false
 */
function removedir($dir)
{
    // 如果目录不存在,返回false
    if (!file_exists($dir) || !is_dir($dir)) {
        return false;
    }

    // 返回真实路径
    $path = realpath($dir);

    // 禁止删除高危系统目录
    if (DIRECTORY_SEPARATOR === '/') {
        // unix环境,要删除的目录路径必须6个(含6个)字符以上
        if (mb_strlen($path) < 6) {
            return false;
        }
    } else {
        // windows环境,要删除的目录路径必须4个(含4个)字符以上
        if (mb_strlen($path) < 4) {
            return false;
        }
    }

    // 递归处理文件和子目录
    $files = scandir($path);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $sub = "$path/$file";
        if (is_dir($sub)) {
            if (!removedir($sub)) {
                return false;
            }
        } else {
            chmod($sub, 0644);
            if (!unlink($sub)) {
                return false;
            }
        }
    }

    // 删除自己
    chmod($path, 0777);
    if (!rmdir($path)) {
        return false;
    }

    // 返回
    return true;
}
