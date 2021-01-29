<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Routing;

use \Dida\Routing\Router;

/**
 * 基于path的路由
 *
 * 1. 如果要对类似 "/foo/bar" 和 "/foo/bar/" 进行一致化处理,可以自行去除$pathinfo尾部的"/"
 *    rtrim($pathinfo)   <-- 注意不要误写为了trim($pathinfo),应为rtrim(...)
 */
class PathRouter extends Router
{
    /**
     * @var array 路由表
     */
    protected $routes = [];

    /**
     * 载入路由表文件
     *
     * @param $filepath 路由表的文件路径
     *
     * @return bool 成功返回true，失败返回false
     *
     * @todo 异常处理
     */
    public function loadRoutes($filepath)
    {
        $this->routes = require $filepath;
    }

    /**
     * 匹配路由表
     *
     * @param string $pathinfo
     *
     * @return bool 匹配成功，返回true。匹配失败，返回false
     */
    public function match($pathinfo)
    {
        // 重置 matchResult
        $this->resetMatchResult();

        // 检查路由表中是否有匹配项
        if (array_key_exists($pathinfo, $this->routes)) {
            $this->routeInfo = [
                'path'       => $pathinfo,
                'callback'   => $this->routes[$pathinfo], // 必填
                'parameters' => [], // 可选
            ];
            $this->matchResult = [
                "code"=> 0,
                "msg" => '',
            ];
            return true;
        } else {
            $this->resetRouteInfo();
            $this->matchResult = [
                'code' => Router::ERROR_MATCH_FAIL,
                'msg'  => 'Route not found'
            ];
            return false;
        }
    }
}
