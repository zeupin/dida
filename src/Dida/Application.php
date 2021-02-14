<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida;

use \Dida\Config;
use \Dida\ServiceBus;

/**
 * Dida\Application 基类
 */
class Application
{
    /**
     * 版本号
     */
    const VERSION = '20210214';

    /**
     * @var \Dida\Http\Request
     */
    protected $_request = null;

    /**
     * @var \Dida\Http\Response
     */
    protected $_response = null;

    /**
     * @var \Dida\Http\Cookie
     */
    protected $_cookie = null;

    /**
     * @var \Dida\Http\Session
     */
    protected $_session = null;

    /**
     * @var \Dida\Routing\Router
     */
    protected $_router = null;

    /**
     * 运行
     */
    public function run()
    {
        // 如果没有设置_router,直接返回
        if ($this->_router === null) {
            exit;
        }

        // 路由
        if ($this->_router->match()) {
            // 如果路由成功,则执行
            if ($this->_router->execute()) {
                // 执行成功
            } else {
                // 执行失败
            }
            exit;
        } else {
            // 如果路由失败
            $this->_router->matchFail();
            exit;
        }
    }

    /**
     * 结束
     *
     * @todo 在代码中提前结束
     */
    public function end()
    {
        exit;
    }

    /**
     * @return \Dida\Http\Request
     */
    public function request()
    {
        if ($this->_request === null) {
            $this->_request = new \Dida\Http\Request;
        }
        return $this->_request;
    }

    /**
     * @return \Dida\Http\Response
     */
    public function response()
    {
        if ($this->_response === null) {
            $this->_response = new \Dida\Http\Response;
        }
        return $this->_response;
    }

    /**
     * @return \Dida\Http\Cookie
     */
    public function cookie()
    {
        if ($this->_cookie === null) {
            $this->_cookie = new \Dida\Http\Cookie;
        }
        return $this->_cookie;
    }

    /**
     * @return \Dida\Http\Session
     */
    public function session()
    {
        if ($this->_session === null) {
            $this->_session = new \Dida\Http\Session;
        }
        return $this->_session;
    }

    /**
     * 设置Router
     */
    public function setRouter($router)
    {
        $this->_router = $router;
    }

    /**
     * 获取Router
     */
    public function getRouter()
    {
        return $this->_router;
    }
}
