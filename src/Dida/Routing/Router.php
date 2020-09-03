<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Routing;

abstract class Router
{
    /**
     * 版本号
     */
    const VERSION = '20200627';

    /**
     * 异常代码
     */
    const SUCCESS = 0;
    const MATCH_EXCEPTION = 1000;
    const CHECK_EXCEPTION = 1001;
    const EXECUTE_EXCEPTION = 1002;

    /**
     * macth()的错误常数
     */
    const ERROR_MATCH_FAIL = 1000; // 没有匹配到任何路由

    /**
     * check()的错误常数
     */
    const ERROR_CALLBACK_INVALID = 2000; // callback无效
    const ERROR_CALLBACK_INVALID_TYPE = 2001; // callable类型无效
    const ERROR_CALLBACK_CONTROLLER_INVALID = 2002; // callback的controller无效
    const ERROR_CALLBACK_ACTION_INVALID = 2003; // callback的action无效

    /**
     * @var mixed 路径信息
     */
    protected $pathinfo = null;

    /**
     * 保存上一次match成功得到的路由详细信息
     */
    protected $routeInfo = [
        'path'       => null, // 可选,route对应的实际path,仅供提示用
        'callback'   => null, // 必填
        'parameters' => [], // 可选,如果需要附加参数,可以放到这里
    ];

    /**
     * 保存上一次match失败的详细信息
     */
    protected $matchError = [
        'code' => 0,
        'msg'  => '',
    ];

    /**
     * 保存上一次execute失败的详细信息
     */
    protected $executeError = [
        'code' => 0,
        'msg'  => '',
    ];

    /**
     * 初始化Router类
     */
    abstract public function __construct();

    /**
     * 获取 pathinfo
     *
     * @return mixed
     */
    public function getPathInfo()
    {
        return $this->pathinfo;
    }

    /**
     * 设置 pathinfo
     *
     * @param $pathinfo 路径信息
     *
     * @return void
     */
    public function setPathInfo($pathinfo)
    {
        $this->pathinfo = $pathinfo;
    }

    /**
     * 匹配路由.
     *
     * 如果匹配成功
     *      $this->matchError = ['code'=>0, 'msg'=>''];
     *      $this->routeInfo  = 匹配到的路由;
     * 如果匹配失败
     *      $this->matchError = ['code'=>错误码, 'msg'=>'错误原因'];
     *      $this->routeInfo  = $this->resetRouteInfo();
     *
     * @param mixed $pathinfo 路径信息
     *
     * @return bool 匹配成功,返回true; 匹配失败,返回false.
     */
    abstract public function match($pathinfo);

    /**
     * 开始进行路由流程
     *
     * @return int 成功, 返回0; 失败, 返回错误码
     */
    public function start()
    {
        // 如果match()失败
        if ($this->match() === false) {
            return Router::MATCH_EXCEPTION;
        }

        // 如果check()失败
        if ($this->check() === false) {
            return Router::CHECK_EXCEPTION;
        }

        // 如果execute()失败
        if ($this->execute() === false && $this->executeError['code'] !== 0) {
            return Router::EXECUTE_EXCEPTION;
        }

        // 顺利完成
        return Router::SUCCESS;
    }

    /**
     * 检查routeInfo的callback是否可以执行
     *
     * @return array ['code'=>xxx, 'msg'=>'xxxx']
     */
    public function check()
    {
        // callback
        $callback = $this->routeInfo['callback'];

        // callback 未设置,返回失败
        if (!$callback) {
            return [
                'code' => Router::ERROR_CALLBACK_INVALID,
                'msg'  => 'Invalid callback.',
            ];
        }

        // 普通的callable,正常返回
        if (is_callable($callback)) {
            return [
                'code' => 0,
                'msg'  => '',
            ];
        }

        // 类型不是数组,返回失败
        if (!is_array($callback)) {
            return [
                'code' => Router::ERROR_CALLBACK_INVALID_TYPE,
                'msg'  => 'Invalid callback type.',
            ];
        }

        // 数组的个数不对
        if (count($callback) < 2) {
            return [
                'code' => Router::ERROR_CALLBACK_INVALID,
                'msg'  => 'Invalid callback.',
            ];
        }

        // 获取controller和action
        list($controller, $action) = $callback;

        // controller和action不是字符串
        if (!is_string($controller) || !is_string($action)) {
            return [
                'code' => Router::ERROR_CALLBACK_INVALID,
                'msg'  => 'Invalid callback.',
            ];
        }

        // 如果controller不存在，则返回错误信息
        if (!class_exists($controller)) {
            return [
                'code' => Router::ERROR_CALLBACK_CONTROLLER_INVALID,
                'msg'  => "Invalid callback controller '$controller'.",
            ];
        }

        // 如果action不存在,也没有使用__call()魔术方法
        if (!method_exists($controller, $action)) {
            if (method_exists($controller, '__call')) {
                return [
                    'code' => 0,
                    'msg'  => '',
                ];
            } else {
                return [
                    'code' => Router::ERROR_CALLBACK_ACTION_INVALID,
                    'msg'  => "Invalid callback action '$controller::$action'",
                ];
            }
        }

        // 正常返回
        return [
            'code' => 0,
            'msg'  => '',
        ];
    }

    /**
     * 执行
     *
     * 执行之前,应该先用check()检查callback,确保其能正常执行
     *
     * @return mixed|false 返回执行结果,出错返回false
     */
    public function execute()
    {
        // 重置executeError
        $this->resetExecuteError();

        // callback
        $callback = $this->routeInfo['callback'];

        // 如果callback是数组格式,则生成实例
        if (is_array($callback)) {
            list($controller, $action) = $callback;
            $con = new $controller;
            $callback = [$con, $action];
        }

        // callback的参数
        $params = $this->routeInfo['parameters'];

        // 如果params不是数组, 先把其转换为数组
        if (!is_array($params)) {
            $params = [$params];
        }

        // 执行
        // 成功返回执行结果, 出错返回false
        return call_user_func_array($callback, $params);
    }

    /**
     * 获取 routeInfo
     *
     * @return array
     */
    public function getRouteInfo()
    {
        return $this->routeInfo;
    }

    /**
     * 获取 matchError
     *
     * @return array
     */
    public function getMatchError()
    {
        return $this->matchError;
    }

    /**
     * 获取 executeError
     *
     * @return array
     */
    public function getExecuteError()
    {
        return $this->executeError;
    }

    /**
     * 重置 routeInfo
     *
     * @return array 重置后的数值
     */
    protected function resetRouteInfo()
    {
        $this->routeInfo = [
            'path'       => null,
            'callback'   => null,
            'parameters' => [],
        ];
        return $this->routeInfo;
    }

    /**
     * 重置 matchError
     * 
     * @return array 重置后的数值
     */
    protected function resetMatchError()
    {
        $this->matchError = [
            'code' => 0,
            'msg'  => '',
        ];
        return $this->matchError;
    }

    /**
     * 重置 executeError
     * 
     * @return array 重置后的数值
     */
    protected function resetExecuteError()
    {
        $this->executeError = [
            'code' => 0,
            'msg'  => '',
        ];
        return $this->executeError;
    }
}
