<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db;

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
     * 返回受影响的行数
     *
     * @return int|false PDO执行成功，返回受影响的行数
     *                   PDO执行失败，返回false
     */
    public function getRowsAffected()
    {
        if ($this->errCode === '00000') {
            return $this->pdostatement->rowCount();
        } else {
            return false;
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
     * 参见PHP官方文档的 PDOStatement::fetch()
     *
     * @return mixed|false 成功返回结果
     *                     失败返回false
     */
    public function fetch()
    {
        return $this->pdostatament->fetch();
    }

    /**
     * 参见PHP官方文档的 PDOStatement::fetchAll()
     *
     * @return mixed|false 成功返回结果
     *                     失败返回false
     */
    public function fetchAll()
    {
        return $this->pdostatament->fetchAll();
    }

    /**
     * 参见PHP官方文档的 PDOStatement::fetchColumn()
     */
    public function fetchColumn($col_number = 0)
    {
        return $this->pdostatement->fetchColumn($col_number);
    }
}
