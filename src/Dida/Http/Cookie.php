<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Http;

use Dida\Util\Crypt;

/**
 * Cookie
 *
 * 备注：
 * 1. cookie的 (name + path + domain) 一起构成完整的 cookie 可访问路径，类似于 FQCN。
 *    同一个name, 但是不同的path的cookie，在浏览器里面被视为是两个不同的cookie。
 *    有时候发现明明是删除了cookie项，怎么还有？？大多数就是这个path路径的问题。
 *    对不精通cookie细节的人，这是个大坑。
 * 2. 本类中，所有调用 setcookie() 的地方，都强制设置 $path = $this->path 且 $domain = $this->domain。
 * 3. 将 $this->salt 设为 非空字符串，表示启用cookie值加密机制。
 * 4. 对于某个cookie[name => value]，实际加密密钥为 ($salt + $name)。
 *    这样即使两个cookie的实际值相同，但它们加密后的值也不同。
 * 5. 设置、删除时，指定的cookie项的(name、path、domain)，都要与原Cookie完全一样。
 *    否则，浏览器会视为两个不同的cookie项，从而不予覆盖，导致修改、删除失败。
 */
class Cookie
{
    /**
     * 版本号
     */
    const VERSION = '20200614';

    /**
     * 内部cookies数据。
     *
     * 没有使用PHP默认解析的$_COOKIE，因为$_COOKIE对含有点号的名字做了额外处理，反而导致了一些问题。
     * 详见 Cookie.md
     *
     * @var array
     */
    protected $cookies = [];

    /**
     * 对cookie的值进行安全加密的salt。为空串表示不需要加密。
     *
     * @var string
     */
    protected $salt = '';

    /**
     * cookie的有效网址路径
     *
     * 设置成 '/' 时，Cookie 对整个域名 domain 有效。
     * 如果设置成 '/foo/'， Cookie 仅仅对 domain 中 /foo/ 目录及其子目录有效（比如 /foo/bar/）。
     * 设置为空时，默认是设置 Cookie 时的当前目录。
     *
     * @var string 有效网址路径，默认为'/'，一般设置为App的子目录路径。
     */
    protected $path = '/';

    /**
     * cookie的有效域名
     *
     * @var string 有效域名，默认为''
     */
    protected $domain = '';

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * 解析HTTP请求的HTTP_COOKIE字段，获取cookies数据
     *
     * 1. PHP的$_COOKIE超级变量,会自动把键为a.b的cookie转为键a_b,且这个事情是在源代码里面干的.
     *    所以这个函数自己实现对cookie的解析,不用$_COOKIE.
     *
     * @return void
     */
    public function init()
    {
        // 如果请求没有带cookie，直接返回
        if (!array_key_exists('HTTP_COOKIE', $_SERVER)) {
            $this->cookies = [];
            return;
        }

        // HTTP_COOKIE
        $hc = $_SERVER['HTTP_COOKIE'];

        // 解析出来的cookies
        $cookies = [];

        // 第一次分割
        $items = explode('; ', $hc);

        // 第二次分割
        foreach ($items as $item) {
            try {
                list($name, $value) = explode('=', $item, 2);
            } catch (\Exception $ex) {
                // 解析出来有问题的，直接丢弃
                continue;
            }
            $name = urldecode($name);
            $value = urldecode($value);
            $cookies[$name] = $value;
        }

        // 保存
        $this->cookies = $cookies;
    }

    /**
     * 设置安全salt
     *
     * @param string $salt
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function setSalt($salt)
    {
        if (!is_string($salt)) {
            throw new \InvalidArgumentException('Dida: Cookie加密密钥必须为字符串类型');
        }

        $this->salt = $salt;
    }

    /**
     * 设置cookie的有效网址路径
     *
     * @param string $path
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function setPath($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Dida: Cookie有效路径必须为字符串类型');
        }

        $this->path = $path;
    }

    /**
     * 设置cookie的有效网址路径
     *
     * @param string $domain
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function setDomain($domain)
    {
        if (!is_string($domain)) {
            throw new \InvalidArgumentException('Dida: Cookie有效域名必须为字符串类型');
        }

        $this->domain = $domain;
    }

    /**
     * 设置一个cookie。
     * 各参数设置参见 PHP 的 setcookie 函数。
     *
     * @param string $name
     * @param string $value
     * @param int    $expires
     * @param bool   $secure
     * @param bool   $httponly
     *
     * @return bool
     */
    public function set($name, $value, $expires = 0, $secure = false, $httponly = false)
    {
        // $value不能是object或者array
        if (is_object($value) || is_array($value)) {
            return false;
        }

        // 必须为字符串类型
        $value = strval($value);

        // 设置，参见class备注[1][2]
        return setcookie($name, $value, $expires, $this->path, $this->domain, $secure, $httponly);
    }

    /**
     * 设置一个加密cookie。
     * 加密用类设置的加密key。
     *
     * @param string $name
     * @param string $value
     * @param int    $expires
     * @param bool   $secure
     * @param bool   $httponly
     *
     * @return bool
     */
    public function setSafe($name, $value, $expires = 0, $secure = false, $httponly = false)
    {
        // $value不能是object或者array
        if (is_object($value) || is_array($value)) {
            return false;
        }

        // 必须为字符串类型
        $value = strval($value);

        // 如果value不为空，且加密key不为空，则加密value
        if ($value && $this->salt) {
            // 如果启用了安全加密模式
            $value = Crypt::encrypt($value, $this->salt . $name);

            // 如果加密失败，返回false
            if ($value === false) {
                return false;
            }
        }

        // 设置，参见class备注[1][2]
        return setcookie($name, $value, $expires, $this->path, $this->domain, $secure, $httponly);
    }

    /**
     * 获取指定的cookie的值
     *
     * @param string $name
     *
     * @return string|null 成功返回值，失败返回null
     */
    public function get($name)
    {
        // 返回
        if (array_key_exists($name, $this->cookies)) {
            return $this->cookies[$name];
        }

        // 如果不存在指定的 cookie，返回 null
        return null;
    }

    /**
     * 获取一个加密的cookie值。
     *
     * @param string $name
     *
     * @return string|null 成功返回值，失败返回null
     */
    public function getSafe($name)
    {
        // 如果不存在指定的 cookie，返回 null
        if (!array_key_exists($name, $this->cookies)) {
            return null;
        }

        // 如果加密key为空，直接返回结果
        if ($this->salt === '') {
            return $this->cookies[$name];
        }

        // 解密
        $safekey = $this->salt . $name;
        $result = Crypt::decrypt($this->cookies[$name], $safekey);

        // 解密失败，返回null
        if ($result === false) {
            return null;
        }

        // 返回结果
        return $result;
    }

    /**
     * 获取所有 cookies
     *
     * @return array
     */
    public function getAll()
    {
        return $this->cookies;
    }

    /**
     * 获取cookies的所有键名
     *
     * @return array
     */
    public function getNames()
    {
        return array_keys($this->cookies);
    }

    /**
     * 删除指定的cookie。
     *
     * 本方法只是处理最简单的删除。
     * 对于指定path,doamin的复杂删除，还是要调用set方法来处理。
     *
     * @param string $name
     *
     * @return void
     */
    public function remove($name, $path = null)
    {
        // path
        if (!is_string($path)) {
            $path = $this->path;
        }

        // 如果cookie存在
        if (array_key_exists($name, $this->cookies)) {
            // 删除当前的cookie项
            unset($this->cookies[$name]);

            // 删除$_COOKIE的变量
            $name1 = str_replace('.', '_', $name);
            unset($_COOKIE[$name],$_COOKIE[$name1]);

            // 让浏览器端也删除cookie
            setcookie($name, '', 1, $path, $this->domain);
        }
    }
}
