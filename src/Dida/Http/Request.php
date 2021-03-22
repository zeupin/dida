<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Http;

/**
 * Request HTTP请求
 */
class Request
{
    /**
     * Version
     */
    const VERSION = '20200628';

    /*
     * HTTP Method
     *
     * 返回的method均为大写
     *
     * HTTP 1.0 定义了3个METHOD:
     *    GET  获取资源
     *    POST 新建资源
     *    HEAD 查询资源头
     *
     * HTTP 1.1 定义了5个METHOD:
     *    PUT      更新整个资源
     *    PATCH    更新资源的个别字段
     *    DELETE   删除资源
     *    OPTIONS  查询可选操作
     *    TRACE
     *
     * @var string|false 获取成功返回method, 失败返回false
     */
    protected $method = false;

    /**
     * 对请求URL的解析
     *
     * 解析uri成功, 返回的urlinfo格式
     * [
     *     'path'     => url路径
     *     'query'    => query查询串
     *     'fragment' => 书签
     * ]
     *
     * 解析uri失败, 这个值为false.
     *
     * @var array|false 解析成功,返回结构数组; 失败,返回false
     */
    protected $urlinfo = null;

    /**
     * 客户端ip
     *
     * @var string|false 成功返回ip, 无法获取返回false
     */
    protected $clientIP = false;

    /**
     * HTTP报文头
     *
     * 1. 如果apache_request_headers()函数存在, 用这个函数生成 headers.
     * 2. 否则用 $_SERVER 的 HTTP_* 项生成 headers.
     * 3. headers 的 keys 全部采用小写字母形式.
     *
     * @var array|null 未初始化时是null, 初始化后是数组
     */
    protected $headers = null;

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->methodInit();
        $this->urlinfoInit();
        $this->clientIPInit();
    }

    /**
     * 初始化 $this->method
     *
     * 按照如下优先级：
     * 1、如果$_POST包含 DIDA_REQUEST_METHOD 字段，则以它的值做为请求方式。
     * 2、如果$_POST包含 _METHOD 字段，则以它的值做为请求方式。
     * 3、$_SERVER['REQUEST_METHOD']的值。
     *
     * @return void
     */
    protected function methodInit()
    {
        if (isset($_POST['DIDA_REQUEST_METHOD'])) {
            $this->method = strtoupper($_POST['DIDA_REQUEST_METHOD']);
        } elseif (isset($_POST['_METHOD'])) {
            $this->method = strtoupper($_POST['_METHOD']);
        } elseif (isset($_SERVER['REQUEST_METHOD'])) {
            $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        } else {
            $this->method = false;
        }
    }

    /**
     * 初始化 $this->urlinfo
     *
     * 解析 path，query，fragment
     * 成功，返回一个关联数组。
     * 失败，$this->urlinfo = false。
     *
     * @return void
     *
     * @see \parse_url()
     */
    protected function urlinfoInit()
    {
        // 如果REQUEST_URI不存在
        if (!isset($_SERVER['REQUEST_URI'])) {
            $this->urlinfo = false;
            return;
        }

        // 解析
        $this->urlinfo = parse_url($_SERVER['REQUEST_URI']);
    }

    /**
     * 初始化 $this->clientIP
     *
     * @return void
     */
    protected function clientIPInit()
    {
        $ip = '';

        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_X_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $this->clientIP = $ip;
    }

    /**
     * 初始化 $this->headers
     *
     * 1. 这个操作比较耗时间, 当需要用之前再执行.
     * 2. 某个header的键名, 标准写法是单词首字母大写, 单词中间用"-"连接.
     *    但是这样不利于取值和比对,所以将键名统一为全小写.
     * 3. 为简单起见, 直接从$_SERVER变量的HTTP_*取出报文头的key值.
     *    但是这种方法要求自定义的header的key必须是"FOO-BAR"的形式, 而不允许是
     *    "FOO_BAR"的形式. 即必须用"-"作为连字符, 不准是"_".
     *
     * @return void
     */
    protected function headersInit()
    {
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (is_array($headers)) {
                // 把key转为全小写,便于后续的取值和比对
                $temp = [];
                foreach ($headers as $name => $value) {
                    if (is_string($name)) {
                        $name = strtolower($name);
                    }
                    $temp[$name] = $value;
                }
                $headers = $temp;
            } else {
                $headers = [];
            }
        } else {
            // 从 $_SERVER 的 HTTP_* 项目中解析出 headers 子项
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $name = substr($name, 5);
                    $name = strtolower($name);
                    $name = str_replace('_', '-', $name);
                    $headers[$name] = $value;
                }
            }
        }

        $this->headers = $headers;
    }

    /**
     * $_GET
     *
     * @param string $name
     *
     * @return array|mixed|null
     */
    public function get($name = null)
    {
        if (is_null($name)) {
            return $_GET;
        }

        return $this->arrayValue($_GET, $name);
    }

    /**
     * $_POST
     *
     * @param string $name
     *
     * @return array|mixed|null
     */
    public function post($name = null)
    {
        if (is_null($name)) {
            return $_POST;
        }

        return $this->arrayValue($_POST, $name);
    }

    /**
     * $_REQUEST
     *
     * @param string $name
     *
     * @return array|mixed|null
     */
    public function request($name = null)
    {
        if (is_null($name)) {
            return $_REQUEST;
        }

        return $this->arrayValue($_REQUEST, $name);
    }

    /**
     * $_SERVER
     *
     * @param string $name
     *
     * @return array|mixed|null
     */
    public function server($name = null)
    {
        if (is_null($name)) {
            return $_SERVER;
        }

        return $this->arrayValue($_SERVER, $name);
    }

    /**
     * $_ENV
     *
     * @param string $name
     *
     * @return array|mixed|null
     */
    public function env($name = null)
    {
        if (is_null($name)) {
            return $_ENV;
        }

        return $this->arrayValue($_ENV, $name);
    }

    /**
     * $_COOKIE
     *
     * @param string $name
     *
     * @return array|mixed|null
     */
    public function cookie($name = null)
    {
        if (is_null($name)) {
            return Cookie::getAll();
        }

        return Cookie::get($name);
    }

    /**
     * $_SESSION
     *
     * 特别处理了一下$_SESSION，因为不执行session_start()，就不一定有$_SESSION这个变量。
     *
     * @param string $name
     *
     * @return array|mixed|null
     */
    public function session($name = null)
    {
        // 如果session没有启用
        if (!isset($_SESSION)) {
            if ($name === null) {
                return [];
            }
            return $this->arrayValue([], $name);
        }

        // 返回所有session
        if (is_null($name)) {
            return $_SESSION;
        }

        // 返回指定session
        return $this->arrayValue($_SESSION, $name);
    }

    /**
     * $_FILES
     *
     * 特别处理了一下$_FILES，因为不是上传模式，就不一定有$_FILES这个变量。
     *
     * @param string $name
     *
     * @return array|mixed|null
     */
    public function files($name = null)
    {
        if (!isset($_FILES)) {
            $_FILES = [];
        }

        if (is_null($name)) {
            return $_FILES;
        }

        return $this->arrayValue($_FILES, $name);
    }

    /**
     * 获取Request的method
     *
     * @return string|false
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * uri的路径解析结果
     *
     * @return array|false 成功返回一个uri的结构数组，失败返回false
     */
    public function getUrlInfo()
    {
        return $this->urlinfo;
    }

    /**
     * Request的url路径。
     *
     * @return string|null|false 正常返回路径，没有则返回null，出错返回false
     */
    public function getPath()
    {
        // 如果 getUrlInfoInit() 失败
        if ($this->urlinfo === false) {
            return false;
        }

        if (array_key_exists('path', $this->urlinfo)) {
            return $this->urlinfo['path'];
        } else {
            return null;
        }
    }

    /**
     * Request的查询串。
     *
     * @return string|null|false 正常返回查询串，没有则返回null，出错返回false
     */
    public function getQueryString()
    {
        // 如果 getUrlInfoInit() 失败
        if ($this->urlinfo === false) {
            return false;
        }

        if (array_key_exists('query', $this->urlinfo)) {
            return $this->urlinfo['query'];
        } else {
            return null;
        }
    }

    /**
     * Request的页面书签。
     *
     * @return string|null|false 正常返回书签，没有则返回null，出错返回false
     */
    public function getFragment()
    {
        // 如果 getUrlInfoInit() 失败
        if ($this->urlinfo === false) {
            return false;
        }

        if (array_key_exists('fragment', $this->urlinfo)) {
            return $this->urlinfo['fragment'];
        } else {
            return null;
        }
    }

    /**
     * 获取客户端IP。
     *
     * @return string 正常,返回客户端ip; 获取失败,返回空串
     */
    public function getClientIP()
    {
        return $this->clientIP;
    }

    /**
     * 获取所有headers列表
     */
    public function getHeaders()
    {
        if ($this->headers === null) {
            $this->headersInit();
        }

        return $this->headers;
    }

    /**
     * 获取指定的header
     */
    public function getHeader($name)
    {
        if ($this->headers === null) {
            $this->headersInit();
        }

        $name = strtoupper($name);
        return $this->arrayValue($this->headers, $name);
    }

    /**
     * 是否是一个ajax请求
     *
     * @return bool
     */
    public function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $v = $_SERVER['HTTP_X_REQUESTED_WITH'];
            if (strtolower($v) === 'xmlhttprequest') {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * 获取页面的来源网址。
     *
     * 注意：
     * 1、HTTP的Referer是浏览器发出的，所以有可能被伪造，一般仅作为辅助判断使用。
     *
     * 正常时，以下情况会取到页面Referer：
     * 1、直接用
     * 2、Form提交的表单(POST或GET)
     * 3、含有src的请求（如js的script标签及html中img标签的src属性）
     *
     * 以下情况不会取到页面Referer：
     * 1、从浏览器内书签打开的页面
     * 2、在浏览器地址栏直接输入URL
     * 3、windows桌面上的超链接图标
     * 4、使用JavaScript的Location.href或者是Location.replace()
     * 5、页头中用<mete http-equiv="refresh">形式的页面转向
     * 6、用XML加载地址
     *
     * @return string|false 正常返回取到的HTTP_REFERER。无法获取时，返回false
     */
    public function getReferer()
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            return $_SERVER['HTTP_REFERER'];
        } else {
            return false;
        }
    }

    /**
     * 获取Http的协议名(http/https)。
     *
     * @return string|false 正常返回读取到的schema; 无法获取时，返回false
     */
    public function getSchema()
    {
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            return $_SERVER['REQUEST_SCHEME'];
        } else {
            return false;
        }
    }

    /**
     * 获取路径中去除了基准路径后的剩余部分。
     *
     * URL路径不存在, 返回false
     * URL路径不是以基准路径开头的，返回false。
     *
     * @param string $basePath 基准路径
     *
     * @return string|false 返回去除基准路径后的剩余部分, 失败返回false
     */
    public function getOffsetPath($basePath)
    {
        $path = $this->getPath();

        // $path异常
        if ($path === false) {
            return false;
        }

        // path=null 等效于 path=''
        if ($path === null) {
            $path = '';
        }

        // 统一移除path末尾的/，以便对 “.../foo” 和 “.../foo/” 处理一致
        $path = rtrim($path, '/\\');

        // URL路径等于基准路径，返回空串。
        if ($path === $basePath) {
            return '';
        }

        $len = mb_strlen($basePath);
        if (mb_substr($path, 0, $len) === $basePath) {
            return mb_substr($path, $len);
        } else {
            // 如果不是一个basePath开始, 返回false
            return false;
        }
    }

    /**
     * 一个工具函数。
     * 如果数组中key存在，则返回对应的value，否则返回null。
     *
     * @param array      $array
     * @param int|string $key
     *
     * @return mixed
     */
    protected function arrayValue(array $array, $key)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        } else {
            return null;
        }
    }
}
