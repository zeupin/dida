<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Query;

trait JoinTrait
{
    /**
     * JOIN
     *
     * @param string       $table
     * @param string       $as
     * @param string|array $on
     *
     * @return \Dida\Db\Query\Query $this
     */
    public function join($table, $as, $on)
    {
    }

    /**
     * INNER JOIN
     *
     * @param string       $table
     * @param string       $as
     * @param string|array $on
     *
     * @return \Dida\Db\Query\Query $this
     */
    public function innerJoin($table, $as, $on)
    {
    }

    /**
     * LEFT JOIN
     *
     * @param string       $table
     * @param string       $as
     * @param string|array $on
     *
     * @return \Dida\Db\Query\Query $this
     */
    public function leftJoin($table, $as, $on)
    {
    }

    /**
     * RIGHT JOIN
     *
     * @param string       $table
     * @param string       $as
     * @param string|array $on
     *
     * @return \Dida\Db\Query\Query $this
     */
    public function rightJoin($table, $as, $on)
    {
    }
}
