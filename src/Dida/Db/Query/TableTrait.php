<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Query;

trait TableTrait
{
    /**
     * 返回主表的SQL表达式
     *
     * @return string
     */
    public function sqlMainTable()
    {
        $table = $this->quoteIdentifier($this->mainTable);
        $as = $this->quoteIdentifier($this->mainTableAs);

        if ($this->mainTableAs) {
            $sql = "$table AS $as";
        } else {
            $sql = $table;
        }

        return $sql;
    }
}
