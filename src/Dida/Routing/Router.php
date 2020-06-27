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
     * 上一次match成功的路由详细信息
     */
    protected $matchInfo = [
        'path'       => null, // 可选,对应的path仅供提示用
        'callback'   => null, // 必填
        'parameters' => [], // 可选,如果需要额外参数,可以放到这里
    ];

    /**
     * 上一次match失败的详细信息
     */
    protected $matchError = [
        'code' => 0,
        'msg'  => '',
    ];

    /**
     * 上一次execute失败的详细信息
     */
    protected $executeError = [
        'code' => 0,
        'msg'  => '',
    ];

    /**
     * 类的初始化
     */
    abstract public function __construct();

    /**
     * 匹配路由.
     *
     * 如果匹配成功
     *      $this->matchError = ['code'=>0, 'msg'=>'']
     *      $this->matchInfo  = 匹配到的路由
     * 如果匹配失败
     *      $this->matchError = ['code'=>错误码, 'msg'=>'错误原因']
     *      $this->matchInfo  = $this->resetMatchInfo()
     *
     * @param mixed $pathinfo 路径信息
     *
     * @return bool 匹配成功,返回true; 匹配失败,返回false.
     */
    abstract public function match($pathinfo);

    /**
     * 手动设置pathinfo
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
     * 开始进行路由流程
     */
    public function start()
    {
        // 开始
        if ($this->pathinfo === null) {
            $this->setDefaultPathInfo();
        }

        // 如果match()失败
        if ($this->match() === false) {
            throw new Exception('Router match() fail.', Router::MATCH_EXCEPTION);
        }

        // 如果check()失败
        if ($this->check() === false) {
            throw new Exception('Router match() fail.', Router::CHECK_EXCEPTION);
        }

        // 如果execute()失败
        if ($this->execute() === false && $this->executeError['code'] !== 0) {
            throw new Exception('Router match() fail.', Router::EXECUTE_EXCEPTION);
        }

        // 成功
        return true;
    }

    /**
     * 检查matchInfo的callback是否可以执行
     *
     * @return array ['code'=>xxx, 'msg'=>'xxxx']
     */
    public function check()
    {
        // callback
        $callback = $this->matchInfo['callback'];

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
        $callback = $this->matchInfo['callback'];

        // callback是数组格式,先生成实例
        if (is_array($callback)) {
            list($controller, $action) = $callback;
            $con = new $controller;
            $callback = [$con, $action];
        }

        // callback的参数
        $params = $this->matchInfo['parameters'];

        // 如果params不是数组, 先把其转换为数组
        if (!is_array($params)) {
            $params = [$params];
        }

        // 执行
        // 成功返回执行结果, 出错返回false
        return call_user_func_array($callback, $params);
    }

    /**
     * 获取 matchInfo
     *
     * @return array
     */
    public function getMatchInfo()
    {
        return $this->matchInfo;
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
     * 重置 matchInfo
     */
    protected function resetMatchInfo()
    {
        $this->matchInfo = [
            'callback'   => null,
            'parameters' => [],
            'path'       => null,
        ];
    }

    /**
     * 重置 matchError
     */
    protected function resetMatchError()
    {
        $this->matchError = [
            'code' => 0,
            'msg'  => '',
        ];
    }

    /**
     * 重置 executeError
     */
    protected function resetExecuteError()
    {
        $this->executeError = [
            'code' => 0,
            'msg'  => '',
        ];
    }
}
