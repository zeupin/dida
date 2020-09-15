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
     * WHERE子句
     *
     * $where为空串: 不要WHERE子句
     * $where为字符串: 输出 WHERE $where
     * $where为关联数组: $key=$value，并用AND连接
     * $where为序列数组：（功能预留，可以做更复杂的处理）
     *
     * 如果为复杂表达式，建议用字符串形式。
     *
     * @param array|string $where 条件
     *
     * @return array|false 成功返回array，失败返回false
     */
    protected function whereClause($where)
    {
        // 如果where为字符串
        if (is_string($where)) {
            if (trim($where) === '') {
                $ret = [
                    'sql'    => '',
                    'params' => [],
                ];
                return $ret;
            } else {
                $ret = [
                    'sql'    => "WHERE $where",
                    'params' => [],
                ];
                return $ret;
            }
        }

        // 如果$where为数组
        if (is_array($where)) {
            // 如果为[]空数组，表示不需要WHERE子句
            if (!$where) {
                $ret = [
                    'sql'    => '',
                    'params' => [],
                ];
                return $ret;
            }

            // 取$where的第一个key。
            // 根据这个key判断$where是关联数组还是序列数组，然后根据不同的类型进行处理。
            if (key($where) === 0) {
                // 如果为序列数组
                // todo 预留
                throw new \Exception('"$where" parameter shoule be an assoc array or a string.');
            } else {
                // 如果为关联数组
                $sql = [];
                $params = [];
                foreach ($where as $k => $v) {
                    $sql[] = "{$this->borderchar}$k{$this->borderchar} = ?";
                    $params[] = $v;
                }
                $ret = [
                    'sql'    => 'WHERE ' . implode(" AND ", $sql),
                    'params' => $params,
                ];
                return $ret;
            }
        }

        throw new \Exception('"$where" parameter type is invalid.');
    }

    /**
     * SET子句
     *
     * @param array $row 数据
     *
     * @return array|false 成功返回array，失败返回false
     */
    protected function setClause(array $row)
    {
        // 如果$row为[]，直接抛异常
        if (!$row) {
            throw new \Exception("SET clause can not be blank.");
        }

        // 生成
        $sql = [];
        $params = [];
        foreach ($row as $field => $value) {
            $sql[] = "{$this->borderchar}$field{$this->borderchar} = ?";
            $params[] = $value;
        }
        return [
            'sql'    => "SET " . implode(', ', $sql),
            'params' => $params,
        ];
    }

    /**
     * UPDATE操作
     *
     * @param array        $row   要更新的数据项
     * @param array|string $where 条件。参见 $this->whereClause()。
     *
     * @return \Dida\Db\ResultSet
     */
    public function update(array $row, $where)
    {
        // set子句
        $_set = $this->setClause($row);

        // where子句
        $_where = $this->whereClause($where);

        // 构造sql
        $sql = <<<SQL
UPDATE {$this->borderchar}$this->table{$this->borderchar}
{$_set["sql"]}
{$_where["sql"]}
SQL;

        // 构造params
        $params = array_merge($_set["params"], $_where["params"]);

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
     * @param array|string $where 条件。参见 $this->whereClause()。
     *
     * @return \Dida\Db\ResultSet
     */
    public function delete(array $where)
    {
        $_where = $this->whereClause($where);

        // 构造sql
        $sql = <<<SQL
DELETE FROM {$this->borderchar}$this->table{$this->borderchar}
$_where
SQL;
        // 构造params
        $params = $_where["params"];

        // 执行
        $rs = $this->db->execWrite($sql, $params);

        // 返回 Dida\Db\ResultSet
        return $rs;
    }

    /**
     * SELECT操作
     *
     * @param array|string $fieldlist 字段列表
     * @param array|string $where     条件。参见 $this->whereClause()。
     * @param string       $limit     LIMIT子句
     *
     * @return \Dida\Db\ResultSet
     */
    public function select($fieldlist = '*', $where = '', $limit = '')
    {
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
            throw new \Exception('"$fieldlist" paramater type is invalid.');
        }

        // where子句
        $_where = $this->whereClause($where);

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
{$_where["sql"]}
$_limit
SQL;

        // 构造params
        $params = $_where["params"];

        // 执行
        $rs = $this->db->execRead($sql, $params);

        // 返回 Dida\Db\ResultSet
        return $rs;
    }

    /**
     * COUNT
     *
     * @param array|string $fieldlist 字段列表。从性能原因考虑，这个参数最好设置为表的主键。
     * @param array|string $where     条件。参见 $this->whereClause()。
     * @param string       $limit     LIMIT子句
     *
     * @return int|false 成功返回count，失败返回false
     */
    public function count($fieldlist = '*', array $where = [])
    {
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
        $_where = $this->whereClause($where);

        // 构造sql
        $sql = <<<SQL
SELECT
    COUNT($_fields) AS {$this->borderchar}count{$this->borderchar}
FROM
    {$this->borderchar}$this->table{$this->borderchar}
{$_where["sql"]}
SQL;

        // 构造params
        $params = $_where["params"];

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
