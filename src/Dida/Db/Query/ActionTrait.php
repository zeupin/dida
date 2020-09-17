<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Query;

trait ActionTrait
{
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
UPDATE {$this->left_quote}$this->mainTable{$this->right_quote}
{$_set["sql"]}
{$_where["sql"]}
SQL;

        // 构造params
        $params = array_merge($_set["params"], $_where["params"]);

        // 执行
        $rs = $this->driver->execWrite($sql, $params);

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
INSERT INTO {$this->left_quote}$this->mainTable{$this->right_quote}
    ($_fields)
VALUES
    ($_values)
SQL;

        // 执行
        $rs = $this->driver->execWrite($sql, $params);

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
DELETE FROM {$this->left_quote}$this->mainTable{$this->right_quote}
$_where
SQL;
        // 构造params
        $params = $_where["params"];

        // 执行
        $rs = $this->driver->execWrite($sql, $params);

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
    {$this->left_quote}$this->mainTable{$this->right_quote}
{$_where["sql"]}
$_limit
SQL;

        // 构造params
        $params = $_where["params"];

        // 执行
        $rs = $this->driver->execRead($sql, $params);

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
    {$this->left_quote}$this->mainTable{$this->right_quote}
{$_where["sql"]}
SQL;

        // 构造params
        $params = $_where["params"];

        // 执行
        $rs = $this->driver->execRead($sql, $params);

        // 如果出错，返回false
        if ($rs->fail()) {
            return false;
        }

        // 成功，返回count
        return intval($rs->getColumn("count"));
    }
}
