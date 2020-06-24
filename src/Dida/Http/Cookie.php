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
 * Cookie
 *
 * 备注：
 * 1. cookie的 (name + path + domain) 一起构成完整的 cookie 可访问路径，类似于 FQCN。
 *    同一个name, 但是不同的path的cookie，在浏览器里面被视为是两个不同的cookie。
 *    有时候发现明明是删除了cookie项，怎么还有？？大多数就是这个path路径的问题。
 *    对不精通cookie细节的人，这是个大坑。
 * 2. 本类中，所有调用 setcookie() 的地方，都强制设置 $path = self::$path 且 $domain = self::$domain。
 * 3. 将 self::$key 设为 非空字符串，表示启用cookie值加密机制。
 * 4. 对于某个cookie[name => value]，实际加密密钥为 ($key + $name)。
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
    protected static $cookies = [];

    /**
     * 对cookie的值进行安全加密的加密密钥。为空串表示不需要加密。
     *
     * @var string
     */
    protected static $key = '';

    /**
     * cookie的有效网址路径
     *
     * 设置成 '/' 时，Cookie 对整个域名 domain 有效。
     * 如果设置成 '/foo/'， Cookie 仅仅对 domain 中 /foo/ 目录及其子目录有效（比如 /foo/bar/）。
     * 设置为空时，默认是设置 Cookie 时的当前目录。
     *
     * @var string 有效网址路径，默认为'/'，一般设置为App的子目录路径。
     */
    protected static $path = '/';

    /**
     * cookie的有效域名
     *
     * @var string 有效域名，默认为''
     */
    protected static $domain = '';

    /**
     * 解析HTTP请求的HTTP_COOKIE字段，获取cookies数据
     */
    public static function init()
    {
        // 如果请求没有带cookie，直接返回
        if (!array_key_exists('HTTP_COOKIE', $_SERVER)) {
            self::$cookies = [];
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
        self::$cookies = $cookies;
    }

    /**
     * 设置安全密钥
     *
     * @param string $key
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public static function setKey($key)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException('Dida: Cookie加密密钥必须为字符串类型');
        }

        self::$key = $key;
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
    public static function setPath($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Dida: Cookie有效路径必须为字符串类型');
        }

        self::$path = $path;
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
    public static function setDomain($domain)
    {
        if (!is_string($domain)) {
            throw new \InvalidArgumentException('Dida: Cookie有效域名必须为字符串类型');
        }

        self::$domain = $domain;
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
    public static function set($name, $value, $expires = 0, $secure = false, $httponly = false)
    {
        // $value不能是object或者array
        if (is_object($value) || is_array($value)) {
            return false;
        }

        // 必须为字符串类型
        $value = strval($value);

        // 设置，参见类备注[1][2]
        return setcookie($name, $value, $expires, self::$path, self::$domain, $secure, $httponly);
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
    public static function setSafe($name, $value, $expires = 0, $secure = false, $httponly = false)
    {
        // $value不能是object或者array
        if (is_object($value) || is_array($value)) {
            return false;
        }

        // 必须为字符串类型
        $value = strval($value);

        // 如果value不为空，且加密key不为空，则加密value
        if ($value && self::$key) {
            // 如果启用了安全加密模式
            $value = Crypt::encrypt($value, self::$key . $name);

            // 如果加密失败，返回false
            if ($value === false) {
                return false;
            }
        }

        // 设置，参见类备注[1][2]
        return setcookie($name, $value, $expires, self::$path, self::$domain, $secure, $httponly);
    }

    /**
     * 获取指定的cookie的值
     *
     * @param string $name
     *
     * @return string|null|false
     *                           正常返回 cookie 值
     *                           cookie不存在，返回 null
     */
    public static function get($name)
    {
        // 如果不存在指定的 cookie，返回 null
        if (!array_key_exists($name, self::$cookies)) {
            return null;
        }

        // 返回
        return self::$cookies[$name];
    }

    /**
     * 获取一个加密的cookie值。
     *
     * @param string $name
     *
     * @return string|null 成功返回值，失败返回null
     */
    public static function getSafe($name)
    {
        // 如果不存在指定的 cookie，返回 null
        if (!array_key_exists($name, self::$cookies)) {
            return null;
        }

        // 如果加密key为空，直接返回结果
        if (self::$key === '') {
            return self::$cookies[$name];
        }

        // 解密
        $key = self::$key . $name;
        $result = Crypt::decrypt(self::$cookies[$name], $key);

        // 解密失败，返回null
        if ($result === false) {
            return null;
        }

        // 返回结果
        return $result;
    }

    /**
     * 获取所有 cookies
     */
    public static function getAll()
    {
        return self::$cookies;
    }

    /**
     * 获取cookies的所有键名
     *
     * @return array
     */
    public static function getNames()
    {
        return array_keys(self::$cookies);
    }

    /**
     * 删除指定的cookie。
     *
     * 本方法只是处理最简单的删除。
     * 对于指定path,doamin的复杂删除，还是要调用set方法来处理。
     *
     * @param string $name
     */
    public static function remove($name, $path = null)
    {
        // path
        if (!is_string($path)) {
            $path = self::$path;
        }

        // 如果cookie存在
        if (array_key_exists($name, self::$cookies)) {
            // 删除当前的cookie项
            unset(self::$cookies[$name], $_COOKIE[$name]);

            // 让浏览器端也删除cookie
            setcookie($name, '', 1, $path, self::$domain);
        }
    }
}
