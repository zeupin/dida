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
 * Routing 路由
 */
class Routing
{
    /**
     * 版本号
     */
    const VERSION = '20191123';

    /**
     * @var array $table 路由表
     */
    public static $table = [];


    /**
     * 从指定的路由表文件中载入
     *
     * @param string $filepath
     *
     * @return  bool 成功返回true，失败返回false
     */
    public static function loadTable($filepath)
    {
        if (file_exists($filepath) && is_file($filepath)) {
            $table = include $filepath;
            if (is_array($table)) {
                self::$table = $table;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    /**
     * 开始路由
     */
    public static function start($path)
    {
        // 查询路由表
        if (array_key_exists($path, self::$table)) {
            $route = self::$table[$path];
            $controller = $route[0];
            $action = $route[1];

            // 检查指定的 controller->action 是否存在
            if (method_exists($controller, $action)) {
                // 如果方法存在，则执行
                self::dispatch($path, $controller, $action);
            } else {
                // 如果方法不存在，转入对应处理程序
                self::actionNotFound($path);
            }
        } else {
            // 如果路径不存在，转入对应处理程序
            self::pathNotFound($path);
        }
    }


    /**
     * 分派执行
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     */
    public static function dispatch($path, $controller, $action)
    {
        $controller = new $controller;
        $controller->$action();
    }


    /**
     * 给出的路径如果不在路由表中，执行本方法
     *
     * @param string $path
     */
    public static function pathNotFound($path)
    {
        echo "$path 路径未定义";
    }


    /**
     * 路由表匹配的方法不存在时，执行本方法
     *
     * @param string $path
     */
    public static function actionNotFound($path)
    {
        echo "$path 方法未定义";
    }
}
