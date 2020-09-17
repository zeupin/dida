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
    protected $whereSet;

    /**
     * 新增一个where条件
     *
     * @param string $field 字段
     * @param mixed  $value 值
     * @param string $op    运算符
     *
     * @return \Dida\Db\Query\Query $this
     */
    public function where($field, $value, $op = '')
    {
    }
}
