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
 * 数据表
 */
class Table
{
    /**
     * 版本号
     */
    const VERSION = '20200913';

    /**
     * @var string 数据表名
     */
    protected $table = null;

    /**
     * @var \Dida\Db\Db
     */
    protected $db;

    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var string 名称定界符
     */
    protected $borderchar = '';

    /**
     * __construct
     *
     * @param string      $name
     * @param string      $prefix
     * @param \Dida\Db\Db $db
     */
    public function __construct($name, $prefix, $db)
    {
        $this->table = $prefix . $name;
        $this->db = $db;
        $this->pdo = $db->pdo();
    }

    /**
     * UPDATE操作
     *
     * @param array $row   要更新的数据项
     * @param array $where 条件
     *
     * @return \Dida\Db\ResultSet
     */
    public function update(array $row, array $where)
    {
        // SQL的参数
        $params = [];

        // set子句
        $_set = [];
        foreach ($row as $field => $value) {
            $_set[] = "{$this->borderchar}$field{$this->borderchar} = ?";
            $params[] = $value;
        }
        $_set = implode(", ", $_set);

        // where子句
        $_where = [];
        foreach ($where as $field => $value) {
            $_where[] = "{$this->borderchar}$field{$this->borderchar} = ?";
            $params[] = $value;
        }
        $_where = implode(", ", $_where);

        // 生成SQL语句
        $sql = <<<SQL
UPDATE {$this->borderchar}$this->table{$this->borderchar}
SET
    $_set
WHERE
    $_where
SQL;

        // 执行
        $rs = $this->db->execWrite($sql, $params);

        // 返回 Dida\Db\ResultSet
        return $rs;
    }

    /**
     * INSERT操作
     *
     * @param array $row 要插入的行数据
     *
     * @return \Dida\Db\ResultSet
     */
    public function insert(array $row)
    {
        // SQL的参数
        $params = [];

        // 准备SQL语句
        $_fields = [];
        $_values = [];
        foreach ($row as $field => $value) {
            $_fields[] = "{$this->borderchar}$field{$this->borderchar}";
            $_values[] = '?';
            $params[] = $value;
        }
        $_fields = implode(", ", $_fields);
        $_values = implode(", ", $_values);

        // SQL
        $sql = <<<SQL
INSERT INTO {$this->borderchar}$this->table{$this->borderchar}
    ($_fields)
VALUES
    ($_values)
SQL;

        // 执行
        $rs = $this->db->execWrite($sql, $params);

        // 返回 Dida\Db\ResultSet
        return $rs;
    }

    /**
     * DELETE操作
     *
     * @param array $where 条件
     *
     * @return \Dida\Db\ResultSet
     */
    public function delete(array $where)
    {
        // SQL的参数
        $params = [];

        // where子句
        $_where = [];
        foreach ($where as $field => $value) {
            $_where[] = "{$this->borderchar}$field{$this->borderchar} = ?";
            $params[] = $value;
        }
        $_where = implode(", ", $_where);

        // 生成SQL语句
        $sql = <<<SQL
DELETE FROM {$this->borderchar}$this->table{$this->borderchar}
WHERE
    $_where
SQL;

        // 执行
        $rs = $this->db->execWrite($sql, $params);

        // 返回 Dida\Db\ResultSet
        return $rs;
    }

    /**
     * SELECT操作
     *
     * @param array|string $fieldlist 字段列表
     * @param array        $where     条件
     * @param string       $limit     LIMIT子句
     *
     * @return \Dida\Db\ResultSet
     */
    public function select($fieldlist = '*', array $where = [], $limit = '')
    {
        // SQL的参数
        $params = [];

        // 字段列表
        if (is_string($fieldlist)) {
            $_fields = $fieldlist;
        } elseif (is_array($fieldlist)) {
            $_fields = [];
            foreach ($fieldlist as $as => $field) {
                if (is_int($as)) {
                    $_fields[] = "{$this->borderchar}$field{$this->borderchar}";
                } else {
                    $_fields[] = "{$this->borderchar}$field{$this->borderchar} AS {$this->borderchar}$as{$this->borderchar}";
                }
            }
            $_fields = implode(", ", $_fields);
        } else {
            throw new \Exception("Invalid '\$fieldlist' paramater type.");
        }

        // where子句
        $_where = '';
        if ($where) {
            $_where = [];
            foreach ($where as $field => $value) {
                $_where[] = "{$this->borderchar}$field{$this->borderchar} = ?";
                $params[] = $value;
            }
            $_where = implode(", ", $_where);
            $_where = "WHERE\n    $_where\n";
        }

        // limit子句
        $_limit = '';
        if ($limit) {
            $_limit = "LIMIT $limit";
        }

        // 生成SQL语句
        $sql = <<<SQL
SELECT
    $_fields
FROM
    {$this->borderchar}$this->table{$this->borderchar}
$_where
$_limit
SQL;

        // 执行
        $rs = $this->db->execRead($sql, $params);

        // 返回 Dida\Db\ResultSet
        return $rs;
    }

    /**
     * COUNT
     *
     * @param array|string $fieldlist 字段列表。从性能原因考虑，这个参数最好用表的主键名。
     * @param array        $where     条件
     * @param string       $limit     LIMIT子句
     *
     * @return int|false 成功返回count，失败返回false
     */
    public function count($fieldlist = '*', array $where = [])
    {
        // SQL的参数
        $params = [];

        // 字段列表
        if (is_string($fieldlist)) {
            $_fields = $fieldlist;
        } elseif (is_array($fieldlist)) {
            $_fields = [];
            foreach ($fieldlist as $as => $field) {
                if (is_int($as)) {
                    $_fields[] = "{$this->borderchar}$field{$this->borderchar}";
                } else {
                    $_fields[] = "{$this->borderchar}$field{$this->borderchar} AS {$this->borderchar}$as{$this->borderchar}";
                }
            }
            $_fields = implode(", ", $_fields);
        } else {
            throw new \Exception("Invalid '\$fieldlist' paramater type.");
        }

        // where子句
        $_where = '';
        if ($where) {
            foreach ($where as $field => $value) {
                $_where[] = "{$this->borderchar}$field{$this->borderchar} = ?";
                $params[] = $value;
            }
            $_where = implode(", ", $_where);
            $_where = "WHERE\n    $_where\n";
        }

        // 生成SQL语句
        $sql = <<<SQL
SELECT
    COUNT($_fields) AS {$this->borderchar}count{$this->borderchar}
FROM
    {$this->borderchar}$this->table{$this->borderchar}
$_where
SQL;

        // 执行
        $rs = $this->db->execRead($sql, $params);

        // 如果出错，返回false
        if ($rs->fail()) {
            return false;
        }

        // 成功，返回count
        return intval($rs->getColumn("count"));
    }
}
