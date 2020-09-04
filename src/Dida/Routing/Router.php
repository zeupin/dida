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
    const VERSION = '20200904';

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
     * 保存上一次match的结果
     */
    protected $matchResult = [
        'code' => -1,
        'msg'  => '',
    ];

    /**
     * 保存上一次check的结果
     */
    protected $checkResult = [
        'code' => -1,
        'msg'  => '',
    ];

    /**
     * 保存上一次execute的结果
     */
    protected $executeResult = [
        'code' => -1,
        'msg'  => '',
    ];

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
     *      $this->matchResult = ['code'=>0, 'msg'=>''];
     *      $this->routeInfo  = 匹配到的路由;
     * 如果匹配失败
     *      $this->matchResult = ['code'=>错误码, 'msg'=>'错误原因'];
     *      $this->routeInfo  = $this->resetRouteInfo();
     *
     * @param mixed $pathinfo 路径信息
     *
     * @return bool 匹配成功,返回true; 匹配失败,返回false.
     */
    abstract public function match($pathinfo);

    /**
     * 运行一个完整的路由流程
     * match => check => execute
     *
     * @return int 成功, 返回0; 失败, 返回错误码
     */
    public function run($pathinfo)
    {
        // 如果match()失败
        if ($this->match($pathinfo) === false) {
            return Router::MATCH_EXCEPTION;
        }

        // 如果execute()失败
        if ($this->execute() === false) {
            if ($this->checkResult["code"] !== 0) {
                return Router::CHECK_EXCEPTION;
            }

            if ($this->executeResult["code"] !== 0) {
                return Router::EXECUTE_EXCEPTION;
            }
        }

        // 顺利完成
        return Router::SUCCESS;
    }

    /**
     * 检查routeInfo的callback是否可以执行
     *
     * @return bool 检查通过，返回true。检查失败，返回false。
     *              检查完成后，可以通过 $this->checkResult 变量查看更详细的结果。
     */
    public function check()
    {
        // 重置 checkResult
        $this->resetCheckResult();

        // callback
        $callback = $this->routeInfo['callback'];

        // 如果callback为空，直接返回失败
        if (!$callback) {
            $this->checkResult = [
                'code' => Router::ERROR_CALLBACK_INVALID,
                'msg'  => 'Invalid callback.',
            ];
            return false;
        }

        // 普通的callable,正常返回
        if (is_callable($callback)) {
            $this->checkResult = [
                'code' => 0,
                'msg'  => '',
            ];
            return true;
        }

        // 类型不是数组,返回失败
        if (!is_array($callback)) {
            $this->checkResult = [
                'code' => Router::ERROR_CALLBACK_INVALID_TYPE,
                'msg'  => 'Invalid callback type.',
            ];
            return false;
        }

        // 数组的个数不对
        if (count($callback) < 2) {
            $this->checkResult = [
                'code' => Router::ERROR_CALLBACK_INVALID,
                'msg'  => 'Invalid callback.',
            ];
            return false;
        }

        // 获取controller和action
        list($controller, $action) = $callback;

        // controller和action不是字符串
        if (!is_string($controller) || !is_string($action)) {
            $this->checkResult = [
                'code' => Router::ERROR_CALLBACK_INVALID,
                'msg'  => 'Invalid callback.',
            ];
            return false;
        }

        // 如果controller不存在，则返回错误信息
        if (!class_exists($controller)) {
            $this->checkResult = [
                'code' => Router::ERROR_CALLBACK_CONTROLLER_INVALID,
                'msg'  => "Invalid callback controller '$controller'.",
            ];
            return false;
        }

        // 如果action不存在
        if (!method_exists($controller, $action)) {
            // 如果controller定义了魔术方法__call，可以视为action存在
            if (method_exists($controller, '__call')) {
                $this->checkResult = [
                    'code' => 0,
                    'msg'  => '',
                ];
                return true;
            }

            // 其它情况返回action无效的错误信息
            $this->checkResult = [
                'code' => Router::ERROR_CALLBACK_ACTION_INVALID,
                'msg'  => "Invalid callback action '$controller::$action'",
            ];
            return false;
        }

        // 正常返回
        $this->checkResult = [
            'code' => 0,
            'msg'  => '',
        ];
        return true;
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
        // 先执行check检查。如果check未通过，报错后直接退出函数
        if (!$this->check()) {
            $this->executeResult = $this->checkResult;
            return false;
        }

        // 检查通过，则可以视为正常执行
        $this->executeResult = [
            'code'=> 0,
            'msg' => '',
        ];

        // 提取callback
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
        // call_user_func_array(): 成功返回执行结果, 有错返回false
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
     * 获取 matchResult
     *
     * @return array
     */
    public function getMatchResult()
    {
        return $this->matchResult;
    }

    /**
     * 获取 checkResult
     *
     * @return array
     */
    public function getCheckResult()
    {
        return $this->checkResult;
    }

    /**
     * 获取 executeResult
     *
     * @return array
     */
    public function getExecuteResult()
    {
        return $this->executeResult;
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
     * 重置 matchResult
     *
     * @return array 重置后的数值
     */
    protected function resetMatchResult()
    {
        $this->matchResult = [
            'code' => -1,
            'msg'  => '',
        ];
        return $this->matchResult;
    }

    /**
     * 重置 checkResult
     *
     * @return array 重置后的数值
     */
    protected function resetCheckResult()
    {
        $this->checkResult = [
            'code' => -1,
            'msg'  => '',
        ];
        return $this->checkResult;
    }

    /**
     * 重置 executeResult
     *
     * @return array 重置后的数值
     */
    protected function resetExecuteResult()
    {
        $this->executeResult = [
            'code' => -1,
            'msg'  => '',
        ];
        return $this->executeResult;
    }
}
