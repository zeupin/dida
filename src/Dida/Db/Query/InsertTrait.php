<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Query;

trait InsertTrait
{
    /**
     * INSERT操作
     *
     * @param array $row 要插入的行数据
     *
     * @return \Dida\Db\ResultSet
     */
    public function insert(array $row)
    {
        // SQL的参数
        $params = [];

        // 准备SQL语句
        $_fields = [];
        $_values = [];
        foreach ($row as $field => $value) {
            $_fields[] = "{$this->left_quote}$field{$this->right_quote}";
            $_values[] = '?';
            $params[] = $value;
        }
        $_fields = implode(", ", $_fields);
        $_values = implode(", ", $_values);

        // SQL
        $sql = <<<SQL
INSERT INTO {$this->left_quote}$this->mainTable{$this->right_quote}
    ($_fields)
VALUES
    ($_values)
SQL;

        // 执行
        $rs = $this->driver->execWrite($sql, $params);

        // 返回 Dida\Db\ResultSet
        return $rs;
    }
}
