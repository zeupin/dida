<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Query;

trait DeleteTrait
{
    /**
     * DELETE
     *
     * @param array $wheres 条件。参见 $this->clauseWHERE()。
     *
     * @return \Dida\Db\ResultSet
     */
    public function delete(array $wheres = [])
    {
        if ($wheres) {
            $this->where($wheres);
        }

        $_table = $this->sqlMainTable();
        $_where = $this->clauseWHERE();

        // 构造sql
        $sql = <<<SQL
DELETE FROM $_table
{$_where["sql"]}
SQL;
        // 构造params
        $params = $_where["params"];

        // 执行
        $rs = $this->driver->execWrite($sql, $params);

        // 返回 Dida\Db\ResultSet
        return $rs;
    }
}
