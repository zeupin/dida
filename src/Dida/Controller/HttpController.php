<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Controller;

use \Dida\ServiceBus;

/**
 * http控制器
 */
class HttpController extends Controller
{
    /**
     * 版本号
     */
    const VERSION = '20200913';

    /**
     * @var \Dida\Http\Request
     */
    protected $request = null;

    /**
     * @var \Dida\Http\Response
     */
    protected $response = null;

    /**
     * @var \Dida\Http\Session
     */
    protected $session = null;

    /**
     * @var \Dida\Http\Cookie
     */
    protected $cookie = null;

    /**
     * construct
     */
    public function __construct()
    {
        $this->request = ServiceBus::get('Request');
        $this->response = ServiceBus::get('Response');
        $this->session = ServiceBus::get('Session');
        $this->cookie = ServiceBus::get('Cookie');
    }
}
