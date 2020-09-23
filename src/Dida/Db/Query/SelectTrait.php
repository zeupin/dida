<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Query;

trait SelectTrait
{
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
        // 主表
        $_table = $this->sqlMainTable();

        // 字段列表
        if (is_string($fieldlist)) {
            $_fields = $fieldlist;
        } elseif (is_array($fieldlist)) {
            $_fields = [];
            foreach ($fieldlist as $as => $field) {
                if (is_int($as)) {
                    $_fields[] = $this->quoteIdentifier($field);
                } else {
                    $_fields[] = $this->quoteIdentifier($field) . ' AS ' . $this->quoteIdentifier($as);
                }
            }
            $_fields = implode(", ", $_fields);
        } else {
            throw new \Exception('"$fieldlist" paramater type is invalid.');
        }

        $_join = $this->clauseJOIN();

        // where子句
        $this->where($where);
        $_where = $this->clauseWHERE();

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
    $_table
{$_join["sql"]}
{$_where["sql"]}
$_limit
SQL;

        // 构造params
        $params = array_merge($_join["params"], $_where["params"]);

        // 执行
        $rs = $this->driver->execRead($sql, $params);

        // 返回 Dida\Db\ResultSet
        return $rs;
    }
}
