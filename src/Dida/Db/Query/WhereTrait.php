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
     * WHERE的items
     */
    protected $whereItems = [];

    /**
     * WHERE的逻辑运算，默认为AND
     */
    protected $whereLogic = "AND";

    /**
     * 返回WHERE子句
     *
     * @return array ['sql'=>..., 'params'=>[...]]
     */
    public function clauseWHERE()
    {
        // 如果$whereItems为空
        if (!$this->whereItems) {
            return [
                'sql'   => '',
                'params'=> [],
            ];
        }

        // 合并items
        $item = $this->combineWHERE();

        // 加上 WHERE
        $item['sql'] = "WHERE " . $item['sql'];

        // 返回
        return $item;
    }

    /**
     * 合并whereItems
     *
     * @return array ['sql'=>..., 'params'=>[...]]
     */
    protected function combineWHERE()
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
     * where条件
     *
     * 方式1： where($field, $op, $value)
     * 方式2： where(array $array)
     *              $array 关联数组。 field => value
     *
     * @param string|array $field 字段名，或者名字对
     * @param string       $op    运算符
     * @param mixed        $value 值
     *
     * @return \Dida\Db\Query $this
     */
    public function where($field, $op = '=', $value = null)
    {
        // 如果$field为''或[]
        if (!$field) {
            return $this;
        }

        // 如果$field是字符串
        if (is_string($field)) {
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

            // 完成
            return $this;
        }

        // 如果$field是数组
        if (is_array($field)) {
            foreach ($field as $f => $v) {
                $this->where($f, $op, $v);
            }

            // 完成
            return $this;
        }

        // 其它情况抛异常
        throw new \Exception('Invalid parameter in ' . __METHOD__ . '()');
    }

    /**
     * WHERE: IN
     *
     * @param string $field  字段
     * @param mixed  $values 值
     *
     * @return \Dida\Db\Query $this
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

        // 完成
        return $this;
    }

    /**
     * WHERE: BETWEEN
     *
     * @param string $field  字段
     * @param mixed  $value1 值
     * @param mixed  $value2 值
     *
     * @return \Dida\Db\Query $this
     */
    public function whereBetween($field, $value1, $value2)
    {
        $field = $this->quoteIdentifier($field);
        $item = [
            'sql'    => "($field BETWEEN ? AND ?)",
            'params' => [$value1, $value2],
        ];
        $this->whereItems[] = $item;

        // 完成
        return $this;
    }

    /**
     * WHERE: 对于复杂WHERE表达式，提供这个函数直接设置
     *
     * @param string $sql    SQL
     * @param array  $params 参数
     *
     * @return \Dida\Db\Query $this
     */
    public function whereRaw($sql, array $params)
    {
        $this->whereItems[] = [
            'sql'   => $sql,
            'params'=> $params,
        ];

        // 完成
        return $this;
    }

    /**
     * 设置 $this->wherLogic
     *
     * @return \Dida\Db\Query $this
     */
    public function whereLogic($logic)
    {
        // 如果whereItems不为空，先把前面设置的所有WHERE做一个打包
        if ($this->whereItems) {
            $item = $this->combineWHERE();
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
     * @return \Dida\Db\Query $this
     */
    public function whereOr()
    {
        return $this->whereLogic('OR');
    }

    /**
     * 设置 $this->wherLogic = 'AND'
     *
     * @return \Dida\Db\Query $this
     */
    public function whereAnd()
    {
        return $this->whereLogic('AND');
    }
}
