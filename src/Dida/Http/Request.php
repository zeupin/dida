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
 * Request
 */
class Request
{
    /**
     * Version
     */
    const VERSION = '20191120';

    /*
     * 内部变量
     */
    protected static $method = null;
    protected static $isAjax = null;
    protected static $clientIP = null;
    protected static $headers = null;

    /**
     * @var array 对请求URL的解析
     *            [
     *            'path'     =>
     *            'query'    =>
     *            'fragment' =>
     *            ]
     */
    protected static $urlinfo = null;

    /**
     * $_GET
     *
     * @return array|mixed
     */
    public static function GET($name = null)
    {
        if (is_null($name)) {
            return $_GET;
        }

        return self::arrayValue($name, $_GET);
    }

    /**
     * $_POST
     *
     * @return array|mixed
     */
    public static function POST($name = null)
    {
        if (is_null($name)) {
            return $_POST;
        }

        return self::arrayValue($name, $_POST);
    }

    /**
     * $_COOKIE
     */
    public static function COOKIE($name = null)
    {
        if (is_null($name)) {
            return Cookie::getAll();
        }

        return Cookie::get($name);
    }

    /**
     * $_REQUEST
     */
    public static function REQUEST($name = null)
    {
        if (is_null($name)) {
            return $_REQUEST;
        }

        return self::arrayValue($name, $_REQUEST);
    }

    /**
     * $_SERVER
     */
    public static function SERVER($name = null)
    {
        if (is_null($name)) {
            return $_SERVER;
        }

        return self::arrayValue($name, $_SERVER);
    }

    /**
     * $_ENV
     */
    public static function ENV($name = null)
    {
        if (is_null($name)) {
            return $_ENV;
        }

        return self::arrayValue($name, $_ENV);
    }

    /**
     * $_SESSION
     *
     * 特别处理了一下$_SESSION，因为不执行session_start()，就不一定有$_SESSION这个变量。
     */
    public static function SESSION($name = null)
    {
        if (!isset($_SESSION)) {
            if (is_null($name)) {
                return [];
            } else {
                return null;
            }
        }

        if (is_null($name)) {
            return $_SESSION;
        }

        return self::arrayValue($name, $_SESSION);
    }

    /**
     * $_FILES
     *
     * 特别处理了一下$_FILES，因为不是上传模式，就不一定有$_FILES这个变量。
     */
    public static function FILES($name = null)
    {
        if (!isset($_FILES)) {
            if (is_null($name)) {
                return [];
            } else {
                return null;
            }
        }

        if (is_null($name)) {
            return $_FILES;
        }

        return self::arrayValue($name, $_FILES);
    }

    /**
     * 初始化 self::$isAjax
     */
    protected static function isAjaxInit()
    {
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            self::$isAjax = false;
            return;
        }

        self::$isAjax = (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * 是否是Ajax请求。
     *
     * @return boolean
     */
    public static function isAjax()
    {
        if (self::$isAjax === null) {
            self::isAjaxInit();
        }

        return self::$isAjax;
    }

    /**
     * 初始化 self::$method
     *
     * 按照如下优先级：
     * 1、如果$_POST包含 DIDA_REQUEST_METHOD 字段，则以它的值做为请求方式。
     * 2、如果$_POST包含 _METHOD 字段，则以它的值做为请求方式。
     * 3、$_SERVER['REQUEST_METHOD']的值。
     *
     * @return void
     */
    protected static function getMethodInit()
    {
        if (isset($_POST['DIDA_REQUEST_METHOD'])) {
            $method = strtolower($_POST['DIDA_REQUEST_METHOD']);
        } elseif (isset($_POST['_METHOD'])) {
            $method = strtolower($_POST['_METHOD']);
        } elseif (isset($_SERVER['REQUEST_METHOD'])) {
            $method = strtolower($_SERVER['REQUEST_METHOD']);
        }

        // 只能为：get，post，put，patch，delete，head，options之一
        switch ($method) {
            case 'get': // 获取资源
            case 'post': // 新建资源
            case 'put': // 更新整个资源
            case 'patch': // 更新资源的个别字段
            case 'delete': // 删除资源
            case 'head': // 查询资源头
            case 'options': // 查询可选操作
                self::$method = $method;
                return;
            default:
                self::$method = false;
                return;
        }
    }

    /**
     * 获取Request的method。
     *
     * 如果有POST的DIDA_REQUEST_METHOD字段，则以此字段为准。
     * 没有这个字段，则看是普通的get还是post。
     * 正常返回get，post，put，patch，delete，head，options之一。
     * 如果是非正常值，返回false。
     *
     * @return string|false
     */
    public static function getMethod()
    {
        if (self::$method === null) {
            self::getMethodInit();
        }
        return self::$method;
    }

    /**
     * 初始化 self::$clientIP
     */
    protected static function getClientIPInit()
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_X_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_CLUSTER_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_X_CLUSTER_CLIENT_IP"];
        } elseif (isset($_SERVER["REMOTE_ADDR"])) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip = false; // ip未定义
        }

        self::$clientIP = $ip;
    }

    /**
     * 获取客户端IP。
     *
     * @return string|false 正常返回读取到的ip，无法获取时，返回false
     */
    public static function getClientIP()
    {
        if (self::$clientIP === null) {
            self::getClientIP();
        }
        return self::$clientIP;
    }

    /**
     * 获取Request的协议名(http/https)。
     *
     * @return string|false 正常返回读取到的schema，无法获取时，返回false
     */
    public static function getSchema()
    {
        return (array_key_exists('REQUEST_SCHEME', $_SERVER)) ? $_SERVER['REQUEST_SCHEME'] : false;
    }

    /**
     * 初始化 self::$headers
     */
    protected static function getHeadersInit()
    {
        if (function_exists("apache_request_headers")) {
            $headers = apache_request_headers();
            if (is_array($headers)) {
                self::$headers = $headers;
            } else {
                self::$headers = [];
            }
        } else {
            // todo
            self::$headers = [];
        }
    }

    /**
     * 获取所有headers列表
     */
    public static function getHeaders()
    {
        if (self::$headers === null) {
            self::getHeadersInit();
        }

        return self::$headers;
    }

    /**
     * 获取指定的header
     */
    public static function getHeader($name)
    {
        if (self::$headers === null) {
            self::getHeadersInit();
        }

        return self::arrayValue($name, self::$headers);
    }

    /**
     * 初始化 self::$urlinfo
     */
    protected static function getUrlInfoInit()
    {
        // 解析 path，query，fragment
        // 成功，返回一个关联数组。
        // 失败，self::$urlinfo = false。
        self::$urlinfo = parse_url($_SERVER["REQUEST_URI"]);

        // 成功后，做下标准化处理
        if (self::$urlinfo) {
            // 统一移除path末尾的/，以便对 “.../foo” 和 “.../foo/” 处理一致。
            self::$urlinfo["path"] = rtrim(self::$urlinfo['path'], "/\\");
        }
    }

    /**
     * 请求的路径解析
     *
     * @return array|false 成功返回一个结构数组，失败返回false
     */
    public static function getUrlInfo()
    {
        // 初始化
        if (self::$urlinfo === null) {
            self::getUrlInfoInit();
        }

        return self::$urlinfo;
    }

    /**
     * Request的url路径。
     *
     * @return string|null|false 正常返回path，没有path则返回null，出错返回false
     */
    public static function getPath()
    {
        // 初始化
        if (self::$urlinfo === null) {
            self::getUrlInfoInit();
        }

        // 如果 getUrlInfoInit() 失败
        if (self::$urlinfo === false) {
            return false;
        }

        return (array_key_exists('path', self::$urlinfo)) ? self::$urlinfo['path'] : null;
    }

    /**
     * Request的查询串。
     *
     * @return string|null|false 正常返回查询串，没有则返回null，出错返回false
     */
    public static function getQueryString()
    {
        // 初始化
        if (self::$urlinfo === null) {
            self::getUrlInfoInit();
        }

        // 如果 getUrlInfoInit() 失败
        if (self::$urlinfo === false) {
            return false;
        }

        return (array_key_exists('query', self::$urlinfo)) ? self::$urlinfo['query'] : null;
    }

    /**
     * Request的页面书签。
     *
     * @return string|null|false 正常返回fragment，没有则返回null，出错返回false
     */
    public static function getFragment()
    {
        // 初始化
        if (self::$urlinfo === null) {
            self::getUrlInfoInit();
        }

        // 如果 getUrlInfoInit() 失败
        if (self::$urlinfo === false) {
            return false;
        }

        return (array_key_exists('fragment', self::$urlinfo)) ? self::$urlinfo['fragment'] : null;
    }

    /**
     * 获取路径中去除了基准路径后的剩余部分。
     *
     * @param string $basePath 基准路径
     *
     * @return false|string
     *                      URL路径不是以基准路径开头的，返回false。
     *                      返回去除基准路径后的剩余部分。
     *                      URL路径等于基准路径，返回空串。
     */
    public static function getPathOffset($basePath)
    {
        $path = self::getPath();

        // $path异常
        if ($path === null) {
            return false;
        }
        if ($path === false) {
            return false;
        }

        // URL路径等于基准路径，返回空串。
        if ($path === $basePath) {
            return '';
        }

        $len = mb_strlen($basePath);
        if (mb_substr($path, 0, $len) === $basePath) {
            return mb_substr($path, $len);
        } else {
            return false;
        }
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
    public static function getReferer()
    {
        if (array_key_exists('HTTP_REFERER', $_SERVER)) {
            return $_SERVER['HTTP_REFERER'];
        } else {
            return false;
        }
    }

    /**
     * 从指定数组中挑出给定的单元。
     *
     * @param string|array $array
     * @param string       $keyN
     *
     * @return array|false 正常返回一个数组，有错返回false。
     *
     * @example
     * \Dida\Request::pick("post","user","pwd")
     * \Dida\Request::pick("post",["user","pwd"])
     * \Dida\Request::pick($_POST,["user","pwd"])
     * 都能得到结果
     * [
     *     "user" => $_POST["user"],
     *     "pwd"  => $_POST["pwd"],
     * ]
     */
    public static function pick($array, $keyN)
    {
        // 准备
        $prepare = self::pickPrepare($array, $keyN);
        if ($prepare === false) {
            return false;
        } else {
            list($array, $keys) = $prepare;
        }

        // 开始
        $result = [];

        // 名称
        foreach ($keys as $key) {
            $result[$key] = self::arrayValue($key, $array);
        }

        // 返回
        return $result;
    }

    /**
     * 从指定数组中挑出除给定的单元以外的所有单元。
     *
     * @param string|array $array
     * @param string       $keyN
     *
     * @return array|false 正常返回一个数组，有错返回false。
     */
    public static function pickExcept($array, $keyN)
    {
        // 准备
        $prepare = self::pickPrepare($array, $keyN);
        if ($prepare === false) {
            return false;
        } else {
            list($array, $keys) = $prepare;
        }

        // 开始
        $result = $array;

        // 名称
        foreach ($keys as $key) {
            unset($result[$key]);
        }

        // 返回
        return $result;
    }

    /**
     * @param array|string $array
     * @param string|array $keyN
     *
     * @return boolean
     */
    protected static function pickPrepare($array, $keyN)
    {
        // $array是字符串
        if (is_string($array)) {
            switch (mb_strtolower($array)) {
                case 'post':
                    $array = $_POST;
                    break;
                case 'get':
                    $array = $_GET;
                    break;
                case 'cookie':
                case 'cookies':
                    $array = self::COOKIE();
                    break;
                case 'session':
                    $array = self::SESSION();
                    break;
                case 'files':
                    $array = self::FILES();
                    break;
                case 'server':
                    $array = $_SERVER;
                    break;
                case 'env':
                    $array = $_ENV;
                    break;
                case 'headers':
                    $array = self::headers();
                    break;
                default:
                    return false;
            }
        } elseif (!is_array($array)) {
            return false;
        }

        // 要排除的键
        $keys = [];
        $cnt = func_num_args();
        if ($cnt === 2) {
            if (is_array($keyN)) {
                $keys = $keyN;
            } elseif (is_string($keyN)) {
                $keys[] = $keyN;
            } else {
                return false;
            }
        } elseif ($cnt > 2) {
            for ($i = 1; $i < $cnt; $i++) {
                $key = func_get_arg($i);
                if (is_string($key) || is_int($key)) {
                    $keys[] = $key;
                } else {
                    return false;
                }
            }
        }

        // 完成
        return [$array, $keys];
    }

    /**
     * 一个工具函数。
     * 如果数组中key存在，则返回对应的value，否则返回null。
     *
     * @param int|string $key
     * @param array      $array
     *
     * @return mixed
     */
    protected static function arrayValue($key, array $array)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        } else {
            return null;
        }
    }
}
