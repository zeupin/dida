<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Console;

class Console
{
    /**
     * 版本号
     */
    const VERSION = '20200612';

    // 粗体
    const BOLD = '1;';

    // 下划线
    const UNDERLINE = '4;';

    // 闪烁
    const SPLASH = '5;';

    // 字符前景色
    const BLACK = '30;';
    const RED = '31;';
    const GREEN = '32;';
    const YELLOW = '33;';
    const BLUE = '34;';
    const MAGENTA = '35;';
    const CYAN = '36;';
    const WHITE = '37;';
    const NORMAL = '39;';

    // 字符背景色
    const BLACK_BG = '40;';
    const RED_BG = '41;';
    const GREEN_BG = '42;';
    const YELLOW_BG = '43;';
    const BLUE_BG = '44;';
    const MAGENTA_BG = '45;';
    const CYAN_BG = '46;';
    const WHITE_BG = '47;';

    /**
     * echo一个值，不换行。可选输出样式。
     */
    public static function pr($msg, $style = null)
    {
        echo self::ss($msg, $style);
    }

    /**
     * echo一个值，输出完后换行。可选输出样式。
     */
    public static function prln($msg, $style = null)
    {
        echo self::ss($msg, $style);
        echo "\n";
    }

    /**
     * 返回一个已被样式格式化的字符串(Styled String)
     *
     * @param string $msg 要显示的字符串
     * @param string $style 样式
     *
     * @return string 样式格式化后的字符串
     *
     * @example
     *      Console::ss("foo bar", Console::CYAN) 用青色显示foo bar
     *      Console::ss("foo bar", Console::RED . Console::BOLD) 用粗体红色显示foo bar
     *      Console::ss("foo bar", Console::WHITE . Console::RED_BG) 用红底白字显示foo bar
     *      Console::ss("foo bar", "40;37;") 专业模式，用红底白字显示foo bar
     */
    public static function ss($msg, $style)
    {
        // 如果不带样式
        if ($style === null) {
            return $msg;
        }

        // 检查style是否非法，非法则直接返回$msg。
        // style只允许(数字和分号)，参见bash的终端字符颜色标准。
        if (preg_match('/[^0-9;]/', $style)) {
            return $msg;
        }

        // 如果最尾巴是';'，去掉
        $style = preg_replace('/;$/', '', $style);

        // 返回样式化后的字符串
        return "\033[${style}m${msg}\033[0m";
    }

    /**
     * 输出一个err类字符串
     */
    public static function err($msg)
    {
        return Console::ss($msg, Console::RED_BG . Console::WHITE);
    }

    /**
     * 输出一个notice类字符串
     */
    public static function notice($msg)
    {
        return Console::ss($msg, Console::MAGENTA);
    }

    /**
     * 输出一个info类字符串
     */
    public static function info($msg)
    {
        return Console::ss($msg, Console::CYAN);
    }
}
