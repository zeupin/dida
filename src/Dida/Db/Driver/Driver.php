<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Driver;

use \PDO;

class Driver
{
    /**
     * 版本号
     */
    const VERSION = '20200907';

    /**
     * 配置项
     *
     * @var array
     */
    protected $conf;

    /**
     * 初始化
     */
    public function __construct(array $conf)
    {
        $this->conf = $conf;
    }

    /**
     * 根据配置，生成PDO对象实例
     *
     * @return \PDO|false 成功返回PDO对象实例；失败返回false。
     */
    public function getPDO()
    {
        // 配置项
        $conf = $this->conf;

        // 如果没有定义dsn字段，则报错
        if (!array_key_exists('dsn', $conf)) {
            return false;
        }

        // 根据PDO规范，依次设置dsn,username,password,driver_options。
        // 参见PDO文档。
        if (!array_key_exists('username', $conf)) {
            return new PDO($conf['dsn']);
        }
        if (!array_key_exists('password', $conf)) {
            return new PDO($conf['dsn'], $conf['username']);
        }
        if (!array_key_exists('options', $conf)) {
            return new PDO($conf['dsn'], $conf['username'], $conf['password']);
        }
        return new PDO($conf['dsn'], $conf['username'], $conf['password'], $conf['options']);
    }
}
