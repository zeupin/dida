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
 * 聚合函数
 */
trait AggregateTrait
{
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
