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
 * 生成JOIN子句
 */
trait JoinTrait
{
    /**
     * join的数据表
     *
     * [
     *   [join类型, 表, 别名, ON条件, 参数数组],
     * ]
     *
     * @var array
     */
    protected $joinItems = [];

    /**
     * 返回JOIN子句
     *
     * @return array ['sql'=>..., 'params'=>[...]]
     */
    public function clauseJOIN()
    {
        $sql = [];
        $params = [];

        foreach ($this->joinItems as $join) {
            list($_jointype, $_table, $_as, $_on, $_params) = $join;

            // _table
            $_table = $this->quoteIdentifier($this->tablePrefix . $_table);

            // _as
            if ($_as) {
                $_as = 'AS ' . $this->quoteIdentifier($_as);
            }

            // _on
            $sql[] = "\n$_jointype $_table $_as ON $_on";

            // _params
            if ($_params) {
                $params = array_merge($params, $_params);
            }
        }

        // 返回
        return [
            'sql'    => implode('', $sql),
            'params' => $params,
        ];
    }

    /**
     * JOIN
     *
     * @param string $table
     * @param string $as
     * @param string $on
     * @param array  $params
     *
     * @return \Dida\Db\Query $this
     */
    public function join($table, $as, $on, array $params = [])
    {
        $this->joinItems[] = ["JOIN", $table, $as, $on, $params];
        return $this;
    }

    /**
     * INNER JOIN
     *
     * @param string $table
     * @param string $as
     * @param string $on
     * @param array  $params
     *
     * @return \Dida\Db\Query $this
     */
    public function innerJoin($table, $as, $on, array $params = [])
    {
        $this->joinItems[] = ["INNER JOIN", $table, $as, $on, $params];
        return $this;
    }

    /**
     * LEFT JOIN
     *
     * @param string $table
     * @param string $as
     * @param string $on
     * @param array  $params
     *
     * @return \Dida\Db\Query $this
     */
    public function leftJoin($table, $as, $on, array $params = [])
    {
        $this->joinItems[] = ["LEFT JOIN", $table, $as, $on, $params];
        return $this;
    }

    /**
     * RIGHT JOIN
     *
     * @param string $table
     * @param string $as
     * @param string $on
     * @param array  $params
     *
     * @return \Dida\Db\Query $this
     */
    public function rightJoin($table, $as, $on, array $params = [])
    {
        $this->joinItems[] = ["RIGHT JOIN", $table, $as, $on, $params];
        return $this;
    }
}
