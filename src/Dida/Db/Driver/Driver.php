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
use \Dida\Db\ResultSet;
use \Dida\Db\Query;

abstract class Driver
{
    /**
     * 版本号
     */
    const VERSION = '20200913';

    /**
     * 配置项
     *
     * @var array
     */
    public $conf;

    /**
     * 生成的PDO实例
     *
     * @var \PDO|null|false 正常\PDO，尚未初始化null，失败或者异常false
     */
    public $pdo = null;

    /**
     * SchemaInfo实例
     *
     * @var \Dida\Db\SchemaInfo
     */
    protected $schemainfo;

    /**
     * __construct
     */
    public function __construct(array $conf)
    {
        $this->conf = $conf;
    }

    /**
     * 初始化
     *
     * 1. console模式下，如果出现报错 PHP Fatal error:  Uncaught PDOException: could not find driver in ...
     *    检查一下dsn是否正确。不能用 --dsn='...'，要用 --dsn="..."，要注意，不然PDO无法识别。
     *
     * @return bool 成功返回true，失败返回false
     */
    public function init()
    {
        // 配置项
        $conf = $this->conf;

        // 如果没有定义dsn字段，则报错
        if (!array_key_exists('dsn', $conf)) {
            $this->pdo = false;
            throw new \Exception('Missing "dsn" option while initializing a PDO instance.');
        }

        // 根据PDO规范，依次设置dsn,username,password,driver_options。
        // 参见PDO文档。
        if (!array_key_exists('username', $conf)) {
            $this->pdo = new PDO($conf['dsn']);
            return true;
        }
        if (!array_key_exists('password', $conf)) {
            $this->pdo = new PDO($conf['dsn'], $conf['username']);
            return true;
        }
        if (!array_key_exists('options', $conf)) {
            $this->pdo = new PDO($conf['dsn'], $conf['username'], $conf['password']);
            return true;
        }
        $this->pdo = new PDO($conf['dsn'], $conf['username'], $conf['password'], $conf['options']);
        return true;
    }

    /**
     * 根据配置，生成PDO对象实例
     *
     * 如果返回了false，需要检查 $conf["dsn"] 是否未设置。
     *
     * @return \PDO|false 正常\PDO, 未初始化null, 失败false
     */
    public function pdo()
    {
        return $this->pdo;
    }

    /**
     * 返回schemainfo实例
     *
     * @return \Dida\Db\SchemaInfo|false 成功返回SchemaInfo实例，失败返回false
     */
    abstract public function schemainfo();

    /**
     * 返回table实例
     *
     * @param string $name 数据表名
     * @param string $as   别名。如果没有别名，设置为''
     *
     * @return \Dida\Db\Query
     */
    abstract public function table($name, $as);

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
