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
    const VERSION = '20200704';

    /**
     * 初始化
     */
    public function __construct()
    {
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
    }

    /**
     * 获取指定的session值
     *
     * @param string $key
     */
    public function get($key)
    {
    }

    /**
     * 设置指定的session
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set($key, $value)
    {
    }

    /**
     * 删除指定的session
     *
     * @param string $key
     */
    public function remove($key)
    {
    }

    /**
     * 获取所有session
     */
    public function getAll()
    {
    }

    /**
     * 销毁当前的session
     */
    public function destory()
    {
    }
}
