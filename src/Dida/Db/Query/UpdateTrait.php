<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Query;

trait UpdateTrait
{
    /**
     * 要更新的数据行
     */
    protected $dataItems = [];

    /**
     * SET子句
     *
     * @param array $row 数据
     *
     * @return array ['sql'=>..., 'params'=>[...]]
     */
    public function clauseSET()
    {
        // 如果rowItems为[]，直接抛异常
        if (!$this->dataItems) {
            throw new \Exception("The data for SET clause is not set.");
        }

        // 生成
        $sql = [];
        $params = [];
        foreach ($this->dataItems as $field => $value) {
            $sql[] = $this->quoteIdentifier($field) . '=?';
            $params[] = $value;
        }

        // 返回
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
    public function update(array $row, $wheres = null)
    {
        // 处理$row
        if ($row) {
            $this->dataItems = $row;
        } else {
            throw new \Exception('Invalid parameter value $row: ' . var_export($row, true));
        }

        // 处理$wheres
        if (!$wheres) {
        } elseif (is_array($wheres)) {
            $this->where($wheres);
        } elseif (is_string($wheres)) {
            $this->whereRaw($wheres, []);
        } else {
            throw new \Exception('Invalid parameter value $wheres.' . var_export($row, true));
        }

        $_table = $this->sqlMainTable();

        // set子句
        $_set = $this->clauseSET();

        // where子句
        $_where = $this->clauseWHERE();

        // 构造sql
        $sql = <<<SQL
UPDATE $_table
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
}
