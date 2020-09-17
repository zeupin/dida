<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Query;

trait FromTrait
{
    public function clauseFrom()
    {
        if ($this->mainTableAlias) {
            $sql = "FROM $this->mainTable AS $this->mainTableAlias";
        } else {
            $sql = "FROM $this->mainTable";
        }

        return [
            "sql"    => $sql,
            "params" => [],
        ];
    }
}
