<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida;

/**
 * SingletonTrait 单例模式特性
 */
trait SingletonTrait
{
    /**
     * 保存类的静态实例
     */
    private static $_instance = null;


    /**
     * 获取类的实例，在首次访问时，生成一个新实例
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }


    /**
     * 禁用 new 操作
     */
    private function __construct()
    {
    }


    /**
     * 禁用 clone 操作
     */
    private function __clone()
    {
    }
}
