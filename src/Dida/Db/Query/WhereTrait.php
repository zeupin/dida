<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Query;

trait WhereTrait
{
    /**
     * WHERE子句
     *
     * @var array [
     *            'sql'    => ...,
     *            'params' => [...],
     *            ]
     */
    protected $clauseWHERE = [];

    /**
     * WHERE的items
     */
    protected $whereItems = [];

    /**
     * WHERE的逻辑运算，默认为AND
     */
    protected $whereLogic = "AND";

    /**
     * 按照whereItems，生成where
     *
     * @return array ['sql'=>..., 'params'=>[...]]
     */
    protected function buildWHERE()
    {
        // 如果whereItems为空，返回空数组
        if (!$this->whereItems) {
            return [];
        }

        // 如果whereItems只有1个，无需处理，直接返回这个item
        if (count($this->whereItems) === 1) {
            return $this->whereItems[0];
        }

        // sql
        $sql = array_column($this->whereItems, 'sql');
        $sql = implode(" {$this->whereLogic} ", $sql);

        // params
        $params = [];
        foreach ($this->whereItems as $item) {
            $params = array_merge($params, $item["params"]);
        }

        // 返回
        return [
            'sql'   => "($sql)",
            'params'=> $params,
        ];
    }

    /**
     * 设置 $this->wherLogic
     *
     * @return \Dida\Db\Query\Query $this
     */
    public function whereLogic($logic)
    {
        // 如果whereItems不为空，先把前面设置的所有WHERE做一个打包
        if ($this->whereItems) {
            $item = $this->buildWHERE();
            $this->whereItems = [$item];
        }

        // 设置新WHERE逻辑
        $this->whereLogic = $logic;

        // 完成
        return $this;
    }

    /**
     * 设置 $this->wherLogic = 'OR'
     *
     * @return \Dida\Db\Query\Query $this
     */
    public function whereOr()
    {
        return $this->whereLogic('OR');
    }

    /**
     * 设置 $this->wherLogic = 'AND'
     *
     * @return \Dida\Db\Query\Query $this
     */
    public function whereAnd()
    {
        return $this->whereLogic('AND');
    }

    /**
     * where条件
     *
     * @param string $field 字段
     * @param mixed  $value 值
     * @param string $op    运算符
     *
     * @return \Dida\Db\Query\Query $this
     */
    public function where($field, $value, $op = '=')
    {
        $op = strtoupper($op);
        switch ($op) {
            case "IN":
                $this->whereIn($field, $value);
                break;
            case "BETWEEN":
                $value1 = $value[0];
                $value2 = $value[1];
                $this->whereBetween($field, $value1, $value2);
                break;
            default:
                $field = $this->quoteIdentifier($field);
                $item = [
                    'sql'    => "($field $op ?)",
                    'params' => [$value],
                ];
                $this->whereItems[] = $item;
        }
    }

    /**
     * WHERE: IN
     *
     * @param string $field  字段
     * @param mixed  $values 值
     *
     * @return \Dida\Db\Query\Query $this
     */
    public function whereIn($field, array $values)
    {
        $field = $this->quoteIdentifier($field);
        $marks = substr(str_repeat('?,'), 0, -1);
        $item = [
            'sql'    => "($field IN ($marks))",
            'params' => [$value1, $value2],
        ];
        $this->whereItems[] = $item;
    }

    /**
     * WHERE: BETWEEN
     *
     * @param string $field 字段
     * @param mixed  $value 值
     * @param string $op    运算符
     *
     * @return \Dida\Db\Query\Query $this
     */
    public function whereBetween($field, $value1, $value2)
    {
        $field = $this->quoteIdentifier($field);
        $item = [
            'sql'    => "($field BETWEEN ? AND ?)",
            'params' => [$value1, $value2],
        ];
        $this->whereItems[] = $item;
    }
}
