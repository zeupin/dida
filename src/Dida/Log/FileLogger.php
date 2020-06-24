<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Log;

use \Dida\Log\Logger;
use \InvalidArgumentException;
use \RuntimeException;

/**
 * FileLogger
 */
class FileLogger extends Logger
{
    /**
     * 版本号
     */
    const VERSION = '20200614';

    /**
     * 日志级别
     *
     * @var array
     */
    protected $levels = [
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
     * 日志保存目录
     *
     * @var string
     */
    protected $path = null;

    /**
     * 初始化
     *
     * @param array $conf   配置项
     */
    public function init(array $conf)
    {
        // 日志的保存路径
        if (array_key_exists('path', $conf)) {
            if (!file_exists($conf['path'])) {
                throw new InvalidArgumentException("设置的日志路径{$conf['path']}不存在");
            }

            if (!is_dir($conf['path'])) {
                throw new InvalidArgumentException("设置的日志路径{$conf['path']}不是合法目录");
            }

            $this->path = realpath($conf['path']);
        } else {
            throw new InvalidArgumentException('未指定日志保存路径path');
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
    public function log($level, $message, array $context = [])
    {
        // 如果指定的level不合法，抛出异常
        if (!array_key_exists($level, $this->levels)) {
            throw new InvalidArgumentException();
        }

        // 准备日志目录
        $dir = $this->prepareLogDir();

        // 要写入的数据
        $data = sprintf("%s [%s] %s\n\n", date('H:i:s'), $level, $message);

        // 写入到级别专用文件
        $file = date('Y_m_d') . ".{$level}.log";
        file_put_contents("$dir/$file", $data, FILE_APPEND);

        // 写入到公用文件
        $file = date('Y_m_d') . '.all.log';
        file_put_contents("$dir/$file", $data, FILE_APPEND);
    }

    /**
     * 处理emergency级日志。
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 处理alert级日志。
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
    public function alert($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
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
    public function critical($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
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
    public function error($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
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
    public function warning($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 处理notice级日志。
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function notice($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
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
    public function info($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 处理debug级日志。
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 返回待使用的日志文件目录
     *
     * @param string $level
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function prepareLogDir()
    {
        if ($this->path === null) {
            throw new InvalidArgumentException('未配置日志保存路径path参数');
        }

        $date = date('Y-m-d');

        $dir = $this->path . "/$date";
        if (!file_exists($dir)) {
            if (mkdir($dir, 0777) === false) {
                throw new RuntimeException('无法创建日志目录');
            }
        }

        return $dir;
    }
}
