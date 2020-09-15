<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db;

use Dida\Util\ArrayEx;

/**
 * 结果集
 */
class ResultSet
{
    /**
     * 版本号
     */
    const VERSION = "20200911";

    /**
     * 执行类型常量
     */
    const EXEC_READ = 'READ';
    const EXEC_WRITE = 'WRITE';

    /**
     * @var string 执行类型（READ/WRITE）
     */
    public $exectype = 'READ';

    /**
     * @var string 错误码
     */
    public $errCode;

    /**
     * @var string 错误信息
     */
    public $errMsg;

    /**
     * @var \PDOStatement PDOStatement实例
     */
    public $pdostatement;

    /**
     * @var string 配置项
     */
    public $options;

    /**
     * 初始化
     *
     * @param string        $errCode
     * @param string        $errMsg
     * @param \PDOStatement $pdostatement
     * @param array         $options
     */
    public function init($errCode, $errMsg, \PDOStatement $pdostatement, array $options)
    {
        $this->errCode = $errCode;
        $this->errMsg = $errMsg;
        $this->pdostatement = $pdostatement;
        $this->options = $options;

        // setFetchMode
        if (!array_key_exists('fetchmode', $options)) {
            // 如果没有设置fetchmode选项，则默认fetchmode为关联数组
            $this->pdostatement->setFetchMode(\PDO::FETCH_ASSOC);
        } else {
            switch ($options["fetchmode"]) {
                case \PDO::FETCH_COLUMN:
                    $this->pdostatement->setFetchMode(\PDO::FETCH_COLUMN, $options["colno"]);
                    break;
                case \PDO::FETCH_CLASS:
                    $this->pdostatement->setFetchMode(\PDO::FETCH_CLASS, $options["classname"], $options["ctorargs"]);
                    break;
                case \PDO::FETCH_INFO:
                    $this->pdostatement->setFetchMode(\PDO::FETCH_CLASS, $options["object"]);
                    break;
                default:
                    if (is_int($options["fetchmode"])) {
                        $this->pdostatement->setFetchMode(intval($options["fetchmode"]));
                    } else {
                        throw new \Exception(1000, "Invalid 'fetchmode' option.");
                    }
            }
        }
    }

    /**
     * SQL是否执行成功
     *
     * @return bool
     */
    public function success()
    {
        return ($this->errCode === '00000');
    }

    /**
     * SQL是否执行失败
     *
     * @return bool
     */
    public function fail()
    {
        return ($this->errCode !== '00000');
    }

    /**
     * 返回受影响的行数
     *
     * @return int|false PDO执行成功，返回受影响的行数
     *                   PDO执行失败，返回false
     */
    public function rowCount()
    {
        if ($this->errCode === '00000') {
            return $this->pdostatement->rowCount();
        } else {
            return false;
        }
    }

    /**
     * 参见PHP官方文档的 PDOStatement::fetch()
     *
     * @return mixed|false 成功返回结果
     *                     失败返回false
     */
    public function fetch()
    {
        return $this->pdostatement->fetch();
    }

    /**
     * 参见PHP官方文档的 PDOStatement::fetchAll()
     *
     * @return mixed|false 成功返回结果
     *                     失败返回false
     */
    public function fetchAll()
    {
        return $this->pdostatement->fetchAll();
    }

    /**
     * 参见PHP官方文档的 PDOStatement::fetchColumn()
     *
     * 注意：fetchColumn()的参数只能是列序号，不能是列名。
     * 如果想用列名，需要使用getColumn()函数。
     *
     * @param int $col_number 列的序号
     *
     * @return mixed|false 成功返回结果
     *                     失败返回false
     */
    public function fetchColumn($col_number)
    {
        return $this->pdostatement->fetchAll();
    }

    /**
     * fetch()的别名
     *
     * @return array
     */
    public function getRow()
    {
        return $this->fetch();
    }

    /**
     * fetchAll()的别名
     *
     * @return array
     */
    public function getRows()
    {
        return $this->fetchAll();
    }

    /**
     * 返回指定的某列的值
     *
     * @param int|string $col 列序号或者列名
     *
     * @return mixed|false 成功返回获取的值
     *                     有错返回false
     */
    public function getColumn($col)
    {
        $row = $this->fetch();

        if (!$row) {
            return false;
        }

        // 返回结果
        return $row[$col];
    }

    /**
     * 返回所有行，并以其中的$col列的值作为键
     *
     * @param int|string $col 用哪一列作为keys
     *
     * @return array
     */
    public function getRowsWithKeys($col)
    {
        $rows = $this->fetchAll();
        $keys = array_column($rows, $col);
        return array_combine($keys, $rows);
    }
}
