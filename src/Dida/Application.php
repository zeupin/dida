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
    protected $_Request = null;

    /**
     * @var \Dida\Http\Response
     */
    protected $_Response = null;

    /**
     * @var \Dida\Http\Cookie
     */
    protected $_Cookie = null;

    /**
     * @var \Dida\Http\Session
     */
    protected $_Session = null;

    /**
     * @var \Dida\Routing\Router
     */
    protected $_Router = null;

    /**
     * 运行
     */
    public function run()
    {
        // 如果没有设置_router,直接返回
        if ($this->_Router === null) {
            exit;
        }

        // 路由
        if ($this->_Router->match()) {
            // 如果路由成功,则执行
            if ($this->_Router->execute()) {
                // 执行成功
            } else {
                // 执行失败
            }
            exit;
        } else {
            // 如果路由失败
            $this->_Router->matchFail();
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
    public function Request()
    {
        return ServiceBus::get('Request');
    }

    /**
     * @return \Dida\Http\Response
     */
    public function Response()
    {
        return ServiceBus::get('Response');
    }

    /**
     * @return \Dida\Http\Cookie
     */
    public function Cookie()
    {
        return ServiceBus::get('Cookie');
    }

    /**
     * @return \Dida\Http\Session
     */
    public function Session()
    {
        return ServiceBus::get('Session');
    }

    /**
     * @return \Dida\Routing\Router
     */
    public function Router()
    {
        return ServiceBus::get('Router');
    }

    /**
     * 获取Db
     *
     * @return \Dida\Db\Db
     */
    public function Db()
    {
        return ServiceBus::get('Db');
    }
}
