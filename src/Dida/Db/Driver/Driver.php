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

abstract class Driver
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
     * 生成的PDO实例
     *
     * @var \PDO|false
     */
    protected $pdo;

    /**
     * SchemaInfo实例
     *
     * @var \Dida\Db\SchemaInfo
     */
    protected $schemainfo;

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
     *                    如果返回了false，需要检查 $conf["dsn"] 是否未设置。
     */
    public function pdo()
    {
        // 配置项
        $conf = $this->conf;

        // 如果没有定义dsn字段，则报错
        if (!array_key_exists('dsn', $conf)) {
            $this->pdo = false;
            return $this->pdo;
        }

        // 根据PDO规范，依次设置dsn,username,password,driver_options。
        // 参见PDO文档。
        if (!array_key_exists('username', $conf)) {
            $this->pdo = new PDO($conf['dsn']);
            return $this->pdo;
        }
        if (!array_key_exists('password', $conf)) {
            $this->pdo = new PDO($conf['dsn'], $conf['username']);
            return $this->pdo;
        }
        if (!array_key_exists('options', $conf)) {
            $this->pdo = new PDO($conf['dsn'], $conf['username'], $conf['password']);
            return $this->pdo;
        }
        $this->pdo = new PDO($conf['dsn'], $conf['username'], $conf['password'], $conf['options']);
        return $this->pdo;
    }

    /**
     * 返回schemainfo实例
     *
     * @return \Dida\Db\SchemaInfo\SchemaInfo|false 成功返回SchemaInfo实例，失败返回false
     */
    abstract public function schemainfo();
}
