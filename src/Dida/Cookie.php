<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * 官网: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida;

/**
 * Cookie
 *
 * 备注：
 * 1. cookie的 (name + path + domain) 一起构成完整的 cookie 可访问路径，类似于 FQCN。
 *    对不精通cookie细节的人，这是个大坑。有时候发现明明是删除了cookie，怎么还有。
 * 2. 本类中，所有调用 setcookie() 的地方，都强制设置 $path = self::$validPath 且 $domain = self::$validDomain。
 * 3. 将 self::$safeKey 设为 非空字符串，表示启用cookie值加密机制。
 * 4. 对于某个cookie[name => value]，实际加密密钥为 ($safeKey + $name)。
 *    这样即使两个cookie的实际值相同，但它们加密后的值也不同。
 */
class Cookie
{
    /**
     * 版本号
     */
    const VERSION = '20191128';

    /**
     * 对cookie的值进行安全加密的加密密钥。为空串表示不需要加密。
     *
     * @var string
     */
    protected static $safeKey = '';

    /**
     * cookie的有效网址路径
     *
     * 设置成 '/' 时，Cookie 对整个域名 domain 有效。
     * 如果设置成 '/foo/'， Cookie 仅仅对 domain 中 /foo/ 目录及其子目录有效（比如 /foo/bar/）。
     * 设置为空时，默认是设置 Cookie 时的当前目录。
     *
     * @var string 有效网址路径，默认为'/'，一般设置为App的子目录路径。
     */
    protected static $validPath = '/';

    /**
     * cookie的有效域名
     *
     * @var string 有效域名，默认为''
     */
    protected static $validDomain = '';


    /**
     * 设置安全密钥
     *
     * @param string $safeKey
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public static function setSafeKey($safeKey)
    {
        if (!is_string($safeKey)) {
            throw new InvalidArgumentException('Dida: Cookie加密密钥必须为字符串类型');
        }

        self::$safeKey = $safeKey;
    }


    /**
     * 设置cookie的有效网址路径
     *
     * @param string $validPath
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public static function setValidPath($validPath)
    {
        if (!is_string($validPath)) {
            throw new InvalidArgumentException('Dida: Cookie有效路径必须为字符串类型');
        }

        self::$validPath = $validPath;
    }


    /**
     * 设置cookie的有效网址路径
     *
     * @param string $validDomain
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public static function setValidDomain($validDomain)
    {
        if (!is_string($validDomain)) {
            throw new InvalidArgumentException('Dida: Cookie有效域名必须为字符串类型');
        }

        self::$validDomain = $validDomain;
    }


    /**
     * 设置一个cookie。
     * 各参数设置参见 PHP 的 setcookie 函数
     *
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param bool $secure
     * @param bool $httponly
     *
     * @return bool
     */
    public static function set($name, $value, $expires = 0, $secure = false, $httponly = false)
    {
        // Cookie名字不可为空
        if (!is_string($name) || $name === '') {
            return false;
        }

        // $value不能是object或者array
        if (is_object($value) || is_array($value)) {
            return false;
        }

        if ($value === null) {
            // 如果$value为null或者''，表示要删除这个cookie
            $value = '';
        } elseif (!is_string($value)) {
            // 如果不是字符串，则先将其转为字符串
            $value = strval($value);
        }

        // 如果value不为空，且safekey不为空，则加密value
        if ($value && self::$safeKey) {
            // 如果启用了安全加密模式
            $value = Crypt::encrypt($value, self::$safeKey . $name);

            // 如果加密失败，返回false
            if ($value === false) {
                return false;
            }
        }

        // 设置，参见类备注[1][2]
        return setcookie($name, $value, $expires, self::$validPath, self::$validDomain, $secure, $httponly);
    }


    /**
     * 获取指定的cookie的值
     *
     * @param string $name
     *
     * @return string|null|false
     *     正常返回 cookie 值
     *     cookie不存在，返回 null
     *     对加密的cookie解密失败，返回 null
     */
    public static function get($name)
    {
        // 如果系统变量 $_COOKIE 不存在
        if (!isset($_COOKIE)) {
            return null;
        }

        // 如果不存在指定的 cookie，返回 null
        if (!array_key_exists($name, $_COOKIE)) {
            return null;
        }

        // 如果不需要解密，直接返回结果
        if (self::$safeKey === '') {
            return $_COOKIE[$name];
        }

        // 解密
        $key = self::$safeKey . $name;
        $result = Crypt::decrypt($_COOKIE[$name], $key);

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
        // 快速处理这种特殊情况
        if (self::$safeKey === '') {
            return $_COOKIE;
        }

        // 待返回的数组
        $cookies = [];

        // 获取全部cookie名
        $names = self::getNames();

        // 逐个处理
        if (self::$safeKey === '') {
            foreach ($names as $name) {
                $cookies[$name] = $_COOKIE[$name];
            }
        } else {
            foreach ($names as $name) {
                $key = self::$safeKey . $name;
                $value = Crypt::decrypt($_COOKIE[$name], $key);
                if ($value === false) {
                    // 解密失败，返回null
                    $cookies[$name] = null;
                } else {
                    $cookies[$name] = $value;
                }
            }
        }

        // 返回
        return $cookies;
    }


    /**
     * 获取cookies的所有键名
     *
     * @return array
     */
    public static function getNames()
    {
        return array_keys($_COOKIE);
    }


    /**
     * 删除指定的cookie
     *
     * @param string $name
     */
    public static function remove($name)
    {
        // 如果cookie存在
        if (array_key_exists($name, $_COOKIE)) {
            unset($_COOKIE[$name]);

            // 让浏览器端也删除cookie
            setcookie($name, '', 1, self::$validPath, self::$validDomain);
        }
    }


    /**
     * 删除所有cookies
     */
    public static function clear()
    {
        foreach ($_COOKIE as $name => $value) {
            unset($_COOKIE[$name]);
            setcookie($name, '', 1, self::$validPath, self::$validDomain);
        }
    }
}
