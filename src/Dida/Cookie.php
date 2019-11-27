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
 */
class Cookie
{
    /**
     * 版本号
     */
    const VERSION = '20191127';

    /**
     * cookie项目名的前缀，方便区分不同App所属的cookie
     *
     * @var string
     */
    protected static $prefix = '';

    /**
     * 对cookie的值进行加密处理的加密密钥
     *
     * @var string|null
     */
    protected static $cookieKey = null;


    /**
     * 初始化
     *
     * @param string $prefix
     * @param string $cryptkey
     */
    public static function init($prefix = '', $cookieKey = null)
    {
        if (!is_string($prefix)) {
            throw new InvalidArgumentException('COOKIE分组前缀必须为字符串类型');
        }
        self::$prefix = $prefix;

        if (!is_null($cookieKey) && !is_string($cookieKey)) {
            throw new InvalidArgumentException('COOKIE加密密钥必须为字符串类型');
        }
        self::$cookieKey = $cookieKey;
    }


    /**
     * 设置一个cookie
     * 各参数设置同 PHP 的 setcookie 函数
     *
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     *
     * @return bool
     */
    public static function set($name, $value, $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false)
    {
        // 加上分组处理
        $name = self::$prefix . $name;

        // 如果启用了安全模式
        if (self::$cookieKey !== null) {
            $value = Crypt::encrypt($value, self::$cookieKey);

            // 如果加密失败，返回false
            if ($value === false) {
                return false;
            }
        }

        // 设置
        return setcookie($realname, $value, $expire, $path, $domain, $secure, $httponly);
    }


    /**
     * 获取指定的cookie的值
     *
     * @param string $name
     *
     * @return string|null|false   正常返回 cookie 值
     *                              cookie不存在，返回 null
     *                              对加密的cookie解密失败，返回 null
     */
    public static function get($name)
    {
        // 如果系统变量 $_COOKIE 不存在
        if (!isset($_COOKIE)) {
            return null;
        }

        // 实际的 cookie 名
        $fullname = self::$prefix . $name;

        // 如果不存在指定的 cookie，返回 null
        if (!array_key_exists($fullname, $_COOKIE)) {
            return null;
        }

        // 如果不需要解密，直接返回结果
        if (self::$cookieKey === null) {
            return $_COOKIE[$fullname];
        }

        // 先解密
        $result = Crypt::decrypt($_COOKIE[$fullname], self::$cookieKey);

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
        if ((self::$cookieKey === null) && (self::prefix === '')) {
            return $_COOKIE;
        }

        // 待返回的数组
        $cookies = [];

        // 获取全部cookie名
        $names = self::getNames();

        // 逐个处理
        if (self::$cookieKey === null) {
            foreach ($names as $name) {
                $fullname = self::$prefix . $name;
                $cookies[$name] = $_COOKIE[$fullname];
            }
        } else {
            foreach ($names as $name) {
                $fullname = self::$prefix . $name;
                $value = Crypt::decrypt($_COOKIE[$fullname], self::$cookieKey);
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
     * 获取cookies的所有键名，获取的键名已去除了 prefix
     *
     * @return array
     */
    public static function getNames()
    {
        // 如果没有prefix
        if (self::$prefix === '') {
            return array_keys($_COOKIE);
        }

        // 把分组下的键名过滤出来
        $names = [];
        $len = mb_strlen(self::$prefix);
        foreach ($_COOKIE as $name => $value) {
            if (mb_substr($name, 0, $len) === self::$prefix) {
                $names[] = mb_substr($name, $len);
            }
        }
        return $names;
    }


    /**
     * 获取cookie的全部完整键名，获取的键名包含 prefix
     *
     * @return array
     */
    public static function getAllFullNames()
    {
        // 如果没有prefix
        if (self::$prefix === '') {
            return array_keys($_COOKIE);
        }

        // 把分组下的键名过滤出来
        $names = [];
        $len = mb_strlen(self::$prefix);
        foreach ($_COOKIE as $name => $value) {
            if (mb_substr($name, 0, $len) === self::$prefix) {
                $names[] = $name;
            }
        }
        return $names;
    }


    /**
     * 删除指定的cookie
     *
     * @param string $name
     */
    public static function remove($name)
    {
        $name = self::$prefix . $name;

        // 如果cookie存在
        if (array_key_exists($name, $_COOKIE)) {
            unset($_COOKIE[$name]);

            // 让浏览器端也删除cookie
            setcookie($name, '', 1);
        }
    }


    /**
     * 删除所有cookies
     */
    public static function clear()
    {
        $name = self::getAllFullNames();

        foreach ($names as $name) {
            unset($_COOKIE[$name]);
            setcookie($name, '', 1);
        }
    }
}
