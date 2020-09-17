<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db;

use \Exception;
use \PDO;
use \Dida\Db\ResultSet;

class Db
{
    /**
     * 版本号
     */
    const VERSION = "20200917";

    /**
     * 配置项
     *
     * @var array
     *
     * @example
     * [
     *     'driver'   => "\\Dida\Db\\Driver\\Mysql",                    // 必填
     *     'dsn'      => 'mysql:host=localhost;port=3306;dbname=foo',   // 必填
     *     'username' => 'tom',
     *     'password' => 'jerry',
     *     'options'  => [
     *         PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
     *         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
     *         PDO::ATTR_PERSISTENT         => true
     *     ],
     * ]
     */
    protected $conf;

    /**
     * 保存的driver实例
     *
     * @var \Dida\Db\Driver\Driver
     */
    protected $driver = null;

    /**
     * 开始
     *
     * @param array $conf
     *
     * @return void
     */
    public function __construct(array $conf)
    {
        // 保存到本地
        $this->conf = $conf;

        // 检查driver
        if (!array_key_exists("driver", $conf)) {
            throw new Exception("Missing a required option \"driver\"", 1);
        }

        // 生成新driver实例
        $this->init();
    }

    /**
     * 初始化，生成PDO实例
     *
     * @return bool 成功true，失败false
     */
    public function init()
    {
        // 新生成driver实例
        $this->driver = new $this->conf["driver"]($this->conf);

        // 初始化，连接数据库
        return $this->driver->init();
    }

    /**
     * 获取PDO实例
     *
     * 如果返回了false，需要检查 $conf["dsn"] 是否未设置。
     *
     * @return \PDO|null|false 正常\PDO, 未初始化null, 失败false
     */
    public function pdo()
    {
        return $this->driver->pdo;
    }

    /**
     * 返回 driver->schemainfo()
     *
     * @return \Dida\Db\SchemaInfo
     */
    public function schemainfo()
    {
        return $this->driver->schemainfo();
    }

    /**
     * 返回 driver->table($name, $prefix, $db)
     *
     * @param string $name   数据表名
     * @param string $prefix 数据表名前缀
     * @param string $as     别名
     *
     * @return \Dida\Db\Query\Table
     */
    public function table($name, $prefix = '', $as = '')
    {
        return $this->driver->table($name, $prefix, $as);
    }

    /**
     * 执行一个SQL读操作，返回一个ResultSet
     *
     * @param string $sql
     * @param array  $params
     * @param array  $options
     *
     * @return \Dida\Db\ResultSet 得到的结果集
     */
    public function execRead($sql, array $params = [], array $options = [])
    {
        return $this->driver->execRead($sql, $params, $options);
    }

    /**
     * 执行一个SQL写操作，返回一个ResultSet
     *
     * @param string $sql
     * @param array  $params
     * @param array  $options
     *
     * @return \Dida\Db\ResultSet 得到的结果集
     */
    public function execWrite($sql, array $params = [], array $options = [])
    {
        return $this->driver->execWrite($sql, $params, $options);
    }
}
