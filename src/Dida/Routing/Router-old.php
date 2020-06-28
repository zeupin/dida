<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Routing;

class RouterOld
{
    /**
     * 版本号
     */
    const VERSION = '20200621';

    /**
     * 错误常数
     */
    const ERROR_NOT_MATCHED = -1; // 没有匹配到任何路由
    const ERROR_CALLBACK_INVALID_TYPE = 1; // callback的类型无效(应该是一个数组)
    const ERROR_CALLBACK_INVALID = 2; // callback无效
    const ERROR_CONTROLLER_INVALID = 3; // 控制器无效

    /**
     * @var array 路由表
     */
    protected static $routes = [];

    /**
     * 出错信息
     */
    protected static $errorInfo = [
        'code' => 0,
        'msg'  => '',
        'data' => null
    ];

    /**
     * @param array $routes 初始化路由器
     */
    public static function init(array $routes)
    {
        self::$routes = $routes;
    }

    /**
     * 获取路由表
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * 新增路由表
     */
    public static function addRoutes(array $routes)
    {
        self::$routes = array_merge(self::$routes, $routes);
    }

    /**
     * 从文件中新增路由表
     *
     * @param string $filepath 路由表文件路径
     *
     * @return true|string 成功返回true, 失败返回错误原因.
     */
    public static function addRoutesFromFile($filepath)
    {
        if (file_exists($filepath) && is_file($filepath)) {
            $table = include $filepath;
            if (is_array($table)) {
                self::$routes = array_merge(self::$routes, $table);
                return true;
            } else {
                return 'Invalid route table.';
            }
        } else {
            return 'File not exists.';
        }
    }

    /**
     * 从路由表中匹配路由
     *
     * @return array|string|false
     *                            匹配成功, 返回匹配的callback(一般是以 [controller, action] 形式)
     *                            匹配成功但有错, 返回错误原因说明. 更详细信息可用 Router::errorInfo() 获取.
     *                            失败, 返回 false.
     */
    public static function match($path)
    {
        if (array_key_exists($path, self::$routes)) {
            // 找到定义的回调函数
            $callback = self::$routes[$path];

            // 路由成功, 但是callback不是约定的数组形式
            if (!\is_array($callback)) {
                self::$errorInfo = [
                    'code' => Router::ERROR_CALLBACK_INVALID_TYPE,
                    'msg'  => "The path `$path` matched, but the callback is not an array.",
                    'data' => [
                        'path'     => $path,
                        'callback' => $callback,
                    ]
                ];
                return self::$errorInfo['msg'];
            }

            // 路由成功, 但是callback非法
            if (count($callback) < 2) {
                self::$errorInfo = [
                    'code' => Router::ERROR_CALLBACK_INVALID,
                    'msg'  => "The path `$path` matched, but the callback array is invalid.",
                    'data' => [
                        'path'     => $path,
                        'callback' => $callback,
                    ]
                ];
                return self::$errorInfo['msg'];
            }

            // 获取controller和action
            list($controller, $action) = $callback;

            // 如果controller不存在，则返回错误信息
            if (!class_exists($controller)) {
                self::$errorInfo = [
                    'code' => Router::ERROR_CONTROLLER_INVALID,
                    'msg'  => "The path `$path` matched, but the controller `$controller` is not found.",
                    'data' => [
                        'path'     => $path,
                        'callback' => $callback,
                    ]
                ];
                return self::$errorInfo['msg'];
            }

            // 返回正常的callback
            return $callback;
        } else {
            // 没有匹配到任何路由
            self::$errorInfo = [
                'code' => Router::ERROR_NOT_MATCHED,
                'msg'  => "The path `$path` is not matched.",
            ];
            return false;
        }
    }

    /**
     * 返回最后一次错误信息
     *
     * @return array 错误信息
     *               [
     *               'code' => xxx,
     *               'msg'  => 'xxxxx',
     *               'data' => mixed
     *               ]
     */
    public static function errorInfo()
    {
        return self::$errorInfo;
    }

    /**
     * 执行指定的 callback_array
     *
     * @param array $callback_array 回调数组
     *                              $callback_array 的形式为 ['controllerName', 'actionName', param1, param2, ...], 其中:
     *                              controllerName, actionName 是必须的.
     *                              param1, param2, ... 是可选的, 如果有的话, 会传给 action 作为参数.
     *
     * @return false|mixed 调用失败返回 false, 成功返回 action 的执行结果.
     *
     * @throws \Exception controller类不存在
     */
    public static function execute(array $callback_array)
    {
        // 获取controller和action
        $controller = array_shift($callback_array);
        $action = array_shift($callback_array);
        $params = $callback_array;

        // 检查类是否存在
        if (class_exists($controller)) {
            $con = new $controller;

            // 执行方法
            return \call_user_func_array([$con, $action], $params);
        } else {
            throw new \Exception("Class `$controller` not exists.");
        }
    }
}
