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
    const VERSION = "20210204";

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
     * @var int|false|null DELETE/INSERT/UPDATE 语句影响的行数
     *                     null=未设置, false=执行失败, int=受影响的行数
     */
    public $affectedRows = null;

    /**
     * @var string 配置项
     */
    public $options;

    /**
     * 初始化
     *
     * $options
     *     fetchmode    fetch模式，参见 PDOStatement::setFetchMode()
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
     * 参见PHP官方文档的 PDOStatement::fetch()
     *
     * @param int $fetch_style
     * @param int $cursor_orientation
     * @param int $cursor_offset
     *
     * @return mixed|false 成功返回结果
     *                     失败返回false
     */
    public function fetch()
    {
        switch (func_num_args()) {
            case 0:
                return $this->pdostatement->fetch();
            case 1:
                return $this->pdostatement->fetch(func_get_arg(0));
            case 2:
                return $this->pdostatement->fetch(func_get_arg(0), func_get_arg(1));
            default:
                return $this->pdostatement->fetch(func_get_arg(0), func_get_arg(1), func_get_arg(2));
        }
    }

    /**
     * 参见PHP官方文档的 PDOStatement::fetchAll()
     *
     * @param int   $fetch_style
     * @param mixed $fetch_argument
     * @param array $ctor_args
     *
     * @return mixed|false 成功返回结果
     *                     失败返回false
     */
    public function fetchAll()
    {
        switch (func_num_args()) {
            case 0:
                return $this->pdostatement->fetchAll();
            case 1:
                return $this->pdostatement->fetchAll(func_get_arg(0));
            case 2:
                return $this->pdostatement->fetchAll(func_get_arg(0), func_get_arg(1));
            default:
                return $this->pdostatement->fetchAll(func_get_arg(0), func_get_arg(1), func_get_arg(2));
        }
    }

    /**
     * fetch() 以关联数组形式返回
     *
     * @return array|false
     */
    public function fetchByAssoc()
    {
        $this->pdostatement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * fetchAll() 以关联数组形式返回
     *
     * @return array|false
     */
    public function fetchAllByAssoc()
    {
        $this->pdostatement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * fetch() 以索引数组形式返回
     *
     * @return array|false
     */
    public function fetchByNum()
    {
        $this->pdostatement->fetch(\PDO::FETCH_NUM);
    }

    /**
     * fetchAll() 以索引数组形式返回
     *
     * @return array|false
     */
    public function fetchAllByNum()
    {
        $this->pdostatement->fetchAll(\PDO::FETCH_NUM);
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
