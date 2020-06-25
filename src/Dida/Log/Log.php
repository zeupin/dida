<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Log;

use \Psr\Log\InvalidArgumentException;
use \Psr\Log\LoggerInterface;

/**
 * Log
 */
class Log
{
    /**
     * 版本号
     */
    const VERSION = '20191223';

    /**
     * 处理函数
     *
     * @var array
     */
    protected static $handlers = [
        'emergency' => null,
        'alert'     => null,
        'critical'  => null,
        'error'     => null,
        'warning'   => null,
        'notice'    => null,
        'info'      => null,
        'debug'     => null,
    ];

    /**
     * 日志实例
     *
     * @var LoggerInterface
     */
    protected static $logger = null;

    /**
     * 初始化
     *
     * @param array $settings 设置项数组
     *                        [
     *                        "classname" => Logger类的类名, // 此日志类需要实现 \Psr\Log\LoggerInterface 接口
     *                        ]
     */
    public static function init(array $settings)
    {
        // 对classname的处理
        if (array_key_exists('classname', $settings)) {
            // 生成类实例
            static::$logger = new $settings['classname'];
            // 初始化类实例
            static::$logger->init($settings);
            // 生成callback
            static::$handlers = [
                'emergency' => [static::$logger, 'emergency'],
                'alert'     => [static::$logger, 'alert'],
                'critical'  => [static::$logger, 'critical'],
                'error'     => [static::$logger, 'error'],
                'warning'   => [static::$logger, 'warning'],
                'notice'    => [static::$logger, 'notice'],
                'info'      => [static::$logger, 'info'],
                'debug'     => [static::$logger, 'debug'],
            ];
        }
    }

    /**
     * 任意级别。
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public static function log($level, $message, array $context = [])
    {
        // 如果尚未设置指定级别的log处理器，则什么都不做
        if (static::$handlers[$level] === null) {
            return;
        }

        // 否则，调用设定的处理函数
        call_user_func(static::$handlers[$level], $message, $context);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function emergency($message, array $context = [])
    {
        if (static::$handlers[__FUNCTION__]) {
            call_user_func(static::$handlers[__FUNCTION__], $message, $context);
        }
    }

    /**
     * critical
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function alert($message, array $context = [])
    {
        if (static::$handlers[__FUNCTION__]) {
            call_user_func(static::$handlers[__FUNCTION__], $message, $context);
        }
    }

    /**
     * 处理critical级日志。
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function critical($message, array $context = [])
    {
        if (static::$handlers[__FUNCTION__]) {
            call_user_func(static::$handlers[__FUNCTION__], $message, $context);
        }
    }

    /**
     * 处理error级日志。
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function error($message, array $context = [])
    {
        if (static::$handlers[__FUNCTION__]) {
            call_user_func(static::$handlers[__FUNCTION__], $message, $context);
        }
    }

    /**
     * 处理warning级日志。
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function warning($message, array $context = [])
    {
        if (static::$handlers[__FUNCTION__]) {
            call_user_func(static::$handlers[__FUNCTION__], $message, $context);
        }
    }

    /**
     * 处理notice级日志。
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function notice($message, array $context = [])
    {
        if (static::$handlers[__FUNCTION__]) {
            call_user_func(static::$handlers[__FUNCTION__], $message, $context);
        }
    }

    /**
     * 处理info级日志。
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function info($message, array $context = [])
    {
        if (static::$handlers[__FUNCTION__]) {
            call_user_func(static::$handlers[__FUNCTION__], $message, $context);
        }
    }

    /**
     * 处理debug级日志。
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function debug($message, array $context = [])
    {
        if (static::$handlers[__FUNCTION__]) {
            call_user_func(static::$handlers[__FUNCTION__], $message, $context);
        }
    }
}
