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
     * cookie分组的前缀，方便区分不同App所属的cookie
     *
     * @var string
     */
    protected static $prefix = '';

    /**
     * 对cookie的值进行安全加密的加密密钥。为空串表示不需要加密。
     *
     * @var string
     */
    protected static $safeKey = '';

    /**
     * cookie分组的有效网址路径
     *
     * 设置成 '/' 时，Cookie 对整个域名 domain 有效。
     * 如果设置成 '/foo/'， Cookie 仅仅对 domain 中 /foo/ 目录及其子目录有效（比如 /foo/bar/）。
     * 设置为空时，默认是设置 Cookie 时的当前目录。
     *
     * @var string 默认有效网址路径
     */
    protected static $validPath = '';


    /**
     * 设置cookie的分组前缀名
     *
     * @param string $prefix
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public static function setPrefix($prefix)
    {
        if (!is_string($prefix)) {
            throw new InvalidArgumentException('COOKIE分组前缀必须为字符串类型');
        }

        self::$prefix = $prefix;
    }


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
            throw new InvalidArgumentException('COOKIE加密密钥必须为字符串类型');
        }

        self::$safeKey = $safeKey;
    }


    /**
     * 设置cookie分组的有效网址路径
     *
     * @param type $validPath
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public static function setValidPath($validPath)
    {
        if (!is_string($validPath)) {
            throw new InvalidArgumentException('COOKIE有效路径必须为字符串类型');
        }

        self::$validPath = $validPath;
    }


    /**
     * 设置一个cookie
     * 各参数设置参见 PHP 的 setcookie 函数
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
    public static function set($name, $value, $expire = 0, $path = null, $domain = '', $secure = false, $httponly = false)
    {
        // Cookie名字不可为空
        if (!is_string($name) || $name === '') {
            return false;
        }

        // 加上分组处理
        $fullname = self::$prefix . $name;

        // 如果启用了安全加密模式
        if (self::$safeKey) {
            $value = Crypt::encrypt($value, self::$safeKey . $fullname);

            // 如果加密失败，返回false
            if ($value === false) {
                return false;
            }
        }

        // 对cookie的有效网址路径进行设置
        //
        // 如果为null，设为缺省的网址路径；
        // 如果已设置，则为设置值；
        // 如果不为字符串，视为错误，返回false
        if (is_null($path)) {
            $path = self::$validPath;
        } elseif (!is_string($path)) {
            return false;
        }

        // 设置
        return setcookie($fullname, $value, $expire, $path, $domain, $secure, $httponly);
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
        if (self::$safeKey === '') {
            return $_COOKIE[$fullname];
        }

        // 先解密
        $result = Crypt::decrypt($_COOKIE[$fullname], self::$safeKey . $fullname);

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
        if ((self::$safeKey === '') && (self::prefix === '')) {
            return $_COOKIE;
        }

        // 待返回的数组
        $cookies = [];

        // 获取全部cookie名
        $names = self::getNames();

        // 逐个处理
        if (self::$safeKey === '') {
            foreach ($names as $name) {
                $fullname = self::$prefix . $name;
                $cookies[$name] = $_COOKIE[$fullname];
            }
        } else {
            foreach ($names as $name) {
                $fullname = self::$prefix . $name;
                $value = Crypt::decrypt($_COOKIE[$fullname], self::$safeKey . $fullname);
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
        $fullname = self::$prefix . $name;

        // 如果cookie存在
        if (array_key_exists($fullname, $_COOKIE)) {
            unset($_COOKIE[$fullname]);

            // 让浏览器端也删除cookie
            setcookie($fullname, '', 1, '/');
        }
    }


    /**
     * 删除所有cookies
     */
    public static function clear()
    {
        $fullnames = self::getAllFullNames();

        foreach ($fullnames as $fullname) {
            unset($_COOKIE[$fullname]);
            setcookie($fullname, '', 1, '/');
        }
        
        var_dump($fullnames);
    }
}
