<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Console;

/**
 * 命令行的参数数组, 提供解析、调用功能。
 */
class Arguments
{
    /**
     * 版本号
     */
    const VERSION = '20200620';

    /**
     * @var array 命令行中的参数数组parameters
     */
    public $parameters = [];

    /**
     * @var array 命令行中的选项数组options
     */
    public $options = [];

    /**
     * 解析命令行参数
     *
     * @param array $argv 传入的命令行参数数组
     *
     * @return array
     */
    public function __construct($argv)
    {
        $this->parse($argv);
    }

    /**
     * 解析命令行参数, 把 arguments 解析为 parameters + options
     *
     * @param array $argv 传入的命令行参数数组
     */
    public function parse(array $argv)
    {
        // 参数(第一个字符不是-)
        $parameters = [];

        // 选项(-x 或者 --xxx)
        $options = [];

        // 解析数组
        foreach ($argv as $i => $arg) {
            if (mb_substr($arg, 0, 1) === '-') {
                // 是个option
                $r = preg_split('/=/', $arg, 2);

                // 没有=的话,值设为null
                if (count($r) === 1) {
                    $options[$r[0]] = null;
                } else {
                    $options[$r[0]] = $r[1];
                }
            } else {
                // 是个parameter
                $parameters[] = $arg;
            }
        }

        // 设置解析好的数据
        $this->parameters = $parameters;
        $this->options = $options;
    }

    /**
     * 检查指定的parameter是否存在
     *
     * @param int $index 指定parameters数组中的索引号
     *
     * @return bool
     */
    public function parameterExists($index)
    {
        return array_key_exists($index, $this->parameters);
    }

    /**
     * 检查指定的option是否存在
     *
     * @param string $optionName option的名字(带前面的'-'或者'--')
     *
     * @return bool
     */
    public function optionExists($optionName)
    {
        return array_key_exists($optionName, $this->optionset);
    }

    /**
     * 获取指定的arg
     *
     * @param int $index arg的索引
     *
     * @return string|false 正常返回string, 失败返回false
     */
    public function getParameter($index)
    {
        if ($this->parameterExists($index)) {
            return $this->parameters[$index];
        } else {
            return false;
        }
    }

    /**
     * 获取指定的option
     *
     * @param string $optionName option的名字
     *
     * @return string|array|null|false 正常返回string或array或null, 失败返回false
     */
    public function getOption($optionName)
    {
        if ($this->optionExists($optionName)) {
            return $this->optionset[$optionName];
        } else {
            return false;
        }
    }

    /**
     * 以字符串形式返回parameters数组
     */
    public function joinParameters()
    {
        $parameters = [];

        foreach ($this->parameters as $parameter) {
            $parameters[] = $this->escapeParameter($parameter);
        }

        return implode(' ', $parameters);
    }

    /**
     * 以字符串返回options数组
     */
    public function joinOptions()
    {
        $options = [];

        foreach ($this->options as $name => $value) {
            $options[] = $name . $this->escapeOption($value);
        }

        return implode(' ', $options);
    }

    /**
     * 以字符串形式返回重新整理后的命令行参数和选项
     */
    public function joinParametersAndOptions()
    {
        $s = $this->joinParameters();

        if ($this->options) {
            return "$s " . $this->joinOptions();
        } else {
            return $s;
        }
    }

    /**
     * 返回一个转义后的parameter
     *
     * @param string $parameter
     *                          没有特殊字符  返回 $parameter
     *                          有"         返回 '$parameter'
     *                          没有"       返回 "$parameter"
     *
     * @return string
     */
    public function escapeParameter($parameter)
    {
        if (preg_match('/[^\w-]/', $parameter)) {
            if (mb_strpos($parameter, '"') === false) {
                return "\"$parameter\"";
            } else {
                return "'$parameter'";
            }
        } else {
            return $parameter;
        }
    }

    /**
     * 返回一个转义后的option
     *
     * @param string|null $option
     *                            null        返回 ''
     *                            没有特殊字符  返回 =option
     *                            有"         返回 ='$option'
     *                            没有"       返回 ="$option"
     *
     * @return string
     */
    public function escapeOption($option)
    {
        if ($option === null) {
            return '';
        }

        if (preg_match('/[^\w-]/', $option)) {
            if (mb_strpos($option, '"') === false) {
                return "=\"$option\"";
            } else {
                return "='$option'";
            }
        } else {
            return "=$option";
        }
    }
}
