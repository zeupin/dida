<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Http;

/**
 * Session
 */
class Session
{
    /**
     * 版本号
     */
    const VERSION = '20200706';

    /**
     * 配置项
     *
     * 基本配置项
     *     session.save_path SESSION的保存路径
     *
     * @var array
     */
    protected $conf = [];

    /**
     * 初始化
     *
     * @param array $conf
     *
     * @return void
     */
    public function __construct(array $conf = [])
    {
        // 读入配置
        $this->config($conf);

        // 初始化
        $this->start();
    }

    /**
     * 配置
     *
     * @param array $conf
     *
     * @return void
     */
    public function config(array $conf)
    {
        $this->conf = array_merge($this->conf, $conf);
    }

    /**
     * 开始
     *
     * @return bool
     */
    public function start()
    {
        // Session数据的保存目录
        if (array_key_exists("session.save_path", $this->conf)) {
            session_save_path($this->conf["save_path"]);
        }

        // 开始
        return session_start();
    }

    /**
     * 销毁当前的session.
     *
     * @return bool 成功返回true, 失败返回false.
     */
    public function destory()
    {
        return session_destroy();
    }

    /**
     * 删除所有session变量
     *
     * 注意:
     * 1. 不要试图用unset($_SESSION)来删除$_SESSION的所有变量,这会禁用$_SESSION超级变量.
     *
     * @return bool 成功返回true, 失败返回false
     */
    public function unset()
    {
        return session_unset();
    }

    /**
     * 是否有指定的session
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        if (isset($_SESSION)) {
            return array_key_exists($key, $_SESSION);
        } else {
            return false;
        }
    }

    /**
     * 获取指定的session值
     *
     * @param string $key
     *
     * @return mixed|null 返回session值; key不存在返回null
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return $_SESSION[$key];
        } else {
            return null;
        }
    }

    /**
     * 设置指定的session
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool 成功返回true; 失败返回false.
     */
    public function set($key, $value)
    {
        if (isset($_SESSION)) {
            $_SESSION[$key] = $value;
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除指定的session
     *
     * @param string $key
     *
     * @return void
     */
    public function remove($key)
    {
        if (isset($_SESSION)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * 获取所有session
     */
    public function getAll()
    {
        if (isset($_SESSION)) {
            return $_SESSION;
        } else {
            return [];
        }
    }
}
