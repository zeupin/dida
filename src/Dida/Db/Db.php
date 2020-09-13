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
    const VERSION = "20200908";

    /**
     * 配置项
     *
     * @var array
     */
    protected $conf;

    /**
     * 保存的driver实例
     *
     * @var \Dida\Db\Driver\Driver
     */
    protected $driver = null;

    /**
     * 生成的PDO实例
     *
     * @var \PDO|null|false 初始为null
     *                      init()成功，为生成的PDO实例
     *                      init()失败，则为false
     */
    protected $pdo = null;

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
    }

    /**
     * 初始化，生成PDO实例
     *
     * @return void
     */
    public function init()
    {
        // 生成driver实例
        $this->driver = new $this->conf["driver"]($this->conf);

        // 生成PDO实例
        $this->pdo = $this->driver->pdo();
    }

    /**
     * 返回PDO实例
     *
     * @return \PDO|null|false
     */
    public function pdo()
    {
        return $this->pdo;
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
     *
     * @return \Dida\Db\Table
     */
    public function table($name, $prefix = '')
    {
        return $this->driver->table($name, $prefix, $this);
    }

    /**
     * 执行通用代码
     *
     * 执行SQL后，会设置resultset的code、msg、pdostatement属性
     * 然后在 execRead/execWrite 中设置resultset的data或者rowsAffected
     *
     * 特别注意！
     * [1] 如果$sql有语法错误，但是在PDO->execute()后，errorCode依然会为"00000"，有点奇怪，需要注意。
     *
     * @param string $sql
     * @param array  $params
     * @param array  $options
     *
     * @return \Dida\Db\ResultSet 结果集
     */
    protected function execCommon($sql, array $params = [], array $options = [])
    {
        // 执行标准数据库操作
        $sth = $this->pdo->prepare($sql);
        $sth->execute($params); // [1]

        // 保存本次PDO的errorInfo
        $info = $this->pdo->errorInfo();

        // 标准的SQLSTATE错误码，5位字符串，没有错误时为00000
        $errCode = $info[0];

        // 本次PDO执行正常，errMsg=""
        // 本次PDO执行失败，errMsg="[驱动级错误码]: 驱动级错误信息"
        if ($info[0] === '00000') {
            $errMsg = '';
        } else {
            $errMsg = sprintf("[%s]: %s", $info[1], $info[2]);
        }

        // 为输出做准备
        $resultset = new ResultSet();
        $resultset->init($errCode, $errMsg, $sth, $options);

        // 返回resultset，供下一步处理
        return $resultset;
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
        // 先执行通用处理
        $resultset = $this->execCommon($sql, $params, $options);

        // 标记exectype为读操作
        $resultset->exectype = ResultSet::EXEC_READ;

        // 返回，供后面继续调用
        return $resultset;
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
        // 先执行通用处理
        $resultset = $this->execCommon($sql, $params, $options);

        // 标记exectype为写操作
        $resultset->exectype = ResultSet::EXEC_WRITE;

        // 返回，供后面继续调用
        return $resultset;
    }
}
