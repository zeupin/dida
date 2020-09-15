<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Query;

/**
 * 数据表
 */
abstract class Table
{
    /**
     * 版本号
     */
    const VERSION = '20200913';

    /**
     * 数据表名
     *
     * @var string
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
     * 标识引用符，左
     *
     * @var string
     *
     * @see setIdentifierQuote()
     */
    protected $left_quote = '';

    /**
     * 标识引用符，右
     *
     * @var string
     *
     * @see setIdentifierQuote()
     */
    protected $right_quote = '';

    /**
     * 设置标识符引用字符
     *
     * 1. 对Mysql，左右标识引用字符分别为 ``
     * 2. 对Sqlite，左右标识引用字符分别为 ""
     * 3. 对Access，左右标识引用字符分别为 []
     *
     * @param string $left_quote
     * @param string $right_quote
     *
     * @return void
     */
    abstract protected function setIdentifierQuote();

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

        // 设置标识符引用字符
        $this->setIdentifierQuote();
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
    protected function clauseWHERE($where)
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
                    $sql[] = "{$this->left_quote}$k{$this->right_quote} = ?";
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
    protected function clauseSET(array $row)
    {
        // 如果$row为[]，直接抛异常
        if (!$row) {
            throw new \Exception("SET clause can not be blank.");
        }

        // 生成
        $sql = [];
        $params = [];
        foreach ($row as $field => $value) {
            $sql[] = "{$this->left_quote}$field{$this->right_quote} = ?";
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
     * @param array|string $where 条件。参见 $this->clauseWHERE()。
     *
     * @return \Dida\Db\ResultSet
     */
    public function update(array $row, $where)
    {
        // set子句
        $_set = $this->clauseSET($row);

        // where子句
        $_where = $this->clauseWHERE($where);

        // 构造sql
        $sql = <<<SQL
UPDATE {$this->left_quote}$this->table{$this->right_quote}
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
            $_fields[] = "{$this->left_quote}$field{$this->right_quote}";
            $_values[] = '?';
            $params[] = $value;
        }
        $_fields = implode(", ", $_fields);
        $_values = implode(", ", $_values);

        // SQL
        $sql = <<<SQL
INSERT INTO {$this->left_quote}$this->table{$this->right_quote}
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
     * @param array|string $where 条件。参见 $this->clauseWHERE()。
     *
     * @return \Dida\Db\ResultSet
     */
    public function delete(array $where)
    {
        $_where = $this->clauseWHERE($where);

        // 构造sql
        $sql = <<<SQL
DELETE FROM {$this->left_quote}$this->table{$this->right_quote}
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
     * @param array|string $where     条件。参见 $this->clauseWHERE()。
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
                    $_fields[] = "{$this->left_quote}$field{$this->right_quote}";
                } else {
                    $_fields[] = "{$this->left_quote}$field{$this->right_quote} AS {$this->left_quote}$as{$this->right_quote}";
                }
            }
            $_fields = implode(", ", $_fields);
        } else {
            throw new \Exception('"$fieldlist" paramater type is invalid.');
        }

        // where子句
        $_where = $this->clauseWHERE($where);

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
    {$this->left_quote}$this->table{$this->right_quote}
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
     * @param array|string $where     条件。参见 $this->clauseWHERE()。
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
                    $_fields[] = "{$this->left_quote}$field{$this->right_quote}";
                } else {
                    $_fields[] = "{$this->left_quote}$field{$this->right_quote} AS {$this->left_quote}$as{$this->right_quote}";
                }
            }
            $_fields = implode(", ", $_fields);
        } else {
            throw new \Exception("Invalid '\$fieldlist' paramater type.");
        }

        // where子句
        $_where = $this->clauseWHERE($where);

        // 构造sql
        $sql = <<<SQL
SELECT
    COUNT($_fields) AS {$this->left_quote}count{$this->right_quote}
FROM
    {$this->left_quote}$this->table{$this->right_quote}
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
