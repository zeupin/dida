<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Debug;

/**
 * 代码调试工具集合
 */
class Debug
{
    /**
     * 版本号
     */
    const VERSION = '20200909';

    /**
     * 格式化一个一维数组
     *
     * @param array $array 要格式化的一维数组
     *
     * @return string
     *
     * @example
     *
     * 1. 常规数组形式
     * ["gupiao", "test"]
     *
     * 2. 关联数组形式
     * ["schema" => "test", "charset" => "latin1", "collation" => "latin1_swedish_ci"]
     */
    public static function formatArray1(array $array)
    {
        $s = [];
        $last = 0;
        foreach ($array as $key => $value) {
            if ($key === $last) {
                $key = null;
                $last++;
            } else {
                $last = false;
            }
            $s[] = self::formatArrayItem($key, $value);
        }
        $s = implode(", ", $s);
        return "[$s]";
    }

    /**
     * 格式化一个二维数组
     *
     * @param array $array 要格式化的二维数组
     *
     * @return string
     *
     * @example
     * [
     *     "gupiao" => ["schema" => "gupiao", "charset" => "latin1", "collation" => "latin1_swedish_ci"],
     *     "test"   => ["schema" => "test"  , "charset" => "latin1", "collation" => "latin1_swedish_ci"],
     * ]
     */
    public static function formatArray2(array $array)
    {
        /*
         * 生成grid
         */
        $grid = [];
        foreach ($array as $row) {
            $line = [];
            foreach ($row as $col => $v) {
                $line[] = self::formatArrayItem($col, $v);
            }
            $grid[] = $line;
        }

        // grid的行数和列数
        $rownum = count($grid);
        $colnum = count($grid[0]);

        // 待返回的数组
        $ret = [];

        /**
         * 对keys的处理
         */
        $keys = array_keys($array);

        // 统一转为字符串
        foreach ($keys as &$key) {
            if (is_string($key)) {
                $key = "\"$key\"";
            }
        }

        // 计算keys的最大宽度
        $max = self::maxlen($keys);

        // 写入到$ret
        for ($i = 0; $i < $rownum; $i++) {
            $ret[$i] = '    ' . sprintf("%-{$max}s", $keys[$i]) . ' => [';
        }

        // 释放$keys，释放内存
        $keys = null;

        /*
         * 对grid的处理
         */
        $kkk = [];
        for ($j = 0; $j < $colnum; $j++) {
            $cols = array_column($grid, $j);
            $max = self::maxlen($cols);
            for ($i = 0; $i < $rownum; $i++) {
                $kkk[$i][] = sprintf("%-{$max}s", $cols[$i]);
            }
        }

        for ($i = 0; $i < $rownum; $i++) {
            $ret[$i] .= implode(", ", $kkk[$i]) . '],';
        }

        $ret = implode("\n", $ret);
        $ret = "[\n$ret\n]";
        return $ret;
    }

    /**
     * 格式化一个一维或二维数组
     * 
     * 根据数组类型，自动调用一维数组或者二维数组的格式化函数完成格式化。
     *
     * @param array $array 要格式化的一维或二维数组
     *
     * @return string
     */
    public static function formatArray12(array $array)
    {
        // 把$array的第1个作为判断标准
        $row = reset($array);
        if (is_array($row)) {
            return self::formatArray2($array);
        } else {
            return self::formatArray1($array);
        }
    }

    /**
     * 给出一个数组（每个item为string或者number类型），计算单个item的最大长度是多少
     *
     * @param array $array 字符串数组
     *
     * @return int
     */
    public static function maxlen(array $array)
    {
        $max = 0;
        foreach ($array as $row) {
            $len = strlen($row);
            if ($len > $max) {
                $max = $len;
            }
        }
        return $max;
    }

    /**
     * 格式化一个数组元素
     *
     * (int, value)    生成 int => value
     * (string, value) 生成 "string" => value
     * (null, value)   生成 value
     *
     * @param int|string|null $key
     * @param mixed           $value
     *
     * @return string 生成的字符串
     */
    public static function formatArrayItem($key, $value)
    {
        // 处理key
        if (is_null($key)) {
            $key = '';
        } elseif (is_string($key)) {
            $key = "\"$key\" => ";
        } else {
            $key = "$key => ";
        }

        // 处理value
        switch (gettype($value)) {
            case 'string':
                $value = '"' . addcslashes($value, "\"\\/") . '"';
                break;
            case "integer":
            case "double":
            case "float":
                $value = $value;
                break;
            case "boolean":
                $value = ($value) ? "true" : "false";
                break;
            case "NULL":
                $value = "null";
                break;
            case "array":
                $value = "#Array#";
                break;
            case "object":
                $value = "#Object#";
                break;
            case "resource":
                $value = "#Resource#";
                break;
            default:
                $value = "#Unknown#";
        }

        // 返回处理结果
        return $key . $value;
    }
}
