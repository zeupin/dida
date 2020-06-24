<?php
/**
 * 拷贝目录
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
