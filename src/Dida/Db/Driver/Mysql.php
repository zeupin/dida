<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Driver;

class Mysql extends Driver
{
    /**
     * 版本号
     */
    const VERSION = '20200913';

    /**
     * 返回schemainfo实例
     *
     * @return \Dida\Db\Mysql\MysqlSchemaInfo|false 成功返回SchemaInfo实例，失败返回false
     */
    public function schemainfo()
    {
        // 如果PDO未生成，则返回false
        if (!$this->pdo instanceof \PDO) {
            return false;
        }

        // 创建实例
        $schemainfo = new \Dida\Db\SchemaInfo\Mysql($this->pdo);

        // 返回
        return $schemainfo;
    }

    /**
     * 返回一个MysqlTable实例
     *
     * @param string      $name   数据表名
     * @param string      $prefix 数据表名前缀
     * @param \Dida\Db\Db $db     当前db的实例
     *
     * @return \Dida\Db\Mysql\MysqlTable
     */
    public function table($name, $prefix, $db)
    {
        $table = new \Dida\Db\Mysql\MysqlTable($name, $prefix, $db);
        return $table;
    }
}
