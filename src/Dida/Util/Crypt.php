<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Util;

/**
 * Crypt 加密解密
 */
class Crypt
{
    /**
     * 版本号
     */
    const VERSION = '20191127';

    /**
     * 默认密钥
     *
     * @var string
     */
    public static $defaultKey = 'Dida is a lightweight rapid development framework!';

    /**
     * 默认初始向量（IV）
     */
    public static $defaultIV = 'Dida, Well done!';

    /**
     * 加密
     *
     * @param string      $srcData 要加密的数据
     * @param string|null $key     密钥
     * @param string|null $iv      初始向量
     *
     * @return string|false 成功返回加密后的值，失败返回false
     */
    public static function encrypt($srcData, $key = null, $iv = null)
    {
        if ($key === null) {
            $key = self::$defaultKey;
        }

        if ($iv === null) {
            $iv = self::$defaultIV;
        }

        // 用 openssl_encrypt() 加密
        $bytes = openssl_encrypt($srcData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        // 成功返回加密后的值，失败返回false
        if ($bytes === false) {
            return false;
        } else {
            return base64_encode($bytes);
        }
    }

    /**
     * 解密
     *
     * @param string      $encData 要解密的数据
     * @param string|null $key     密钥
     * @param string|null $iv      初始向量
     *
     * @return string|false 成功返回解密后的值，失败返回false
     */
    public static function decrypt($encData, $key = null, $iv = null)
    {
        if ($key === null) {
            $key = self::$defaultKey;
        }

        if ($iv === null) {
            $iv = self::$defaultIV;
        }

        // 先进行 base64 解码，解码失败直接返回 false
        $bytes = base64_decode($encData);
        if ($bytes === false) {
            return false;
        }

        // 用 openssl_encrypt() 加密，成功返回解密后的值，失败返回false
        return openssl_decrypt($bytes, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}
