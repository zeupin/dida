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
     * 返回一个SchemaInfo
     *
     * @return \Dida\Db\Mysql\SchemaInfo|false 成功返回SchemaInfo实例，失败返回false
     */
    public function schemainfo()
    {
        // 如果PDO未生成，则返回false
        if (!$this->pdo instanceof \PDO) {
            return false;
        }

        // 创建实例
        $schemainfo = new \Dida\Db\Mysql\SchemaInfo($this->pdo);

        // 返回
        return $schemainfo;
    }

    /**
     * 返回一个Table
     *
     * @param string $name   数据表名
     * @param string $prefix 数据表名前缀
     * @param string $as     别名。如果无别名，设置为''
     *
     * @return \Dida\Db\Mysql\Query
     */
    public function table($name, $prefix, $as)
    {
        $table = new \Dida\Db\Mysql\Query($this, $name, $prefix, $as);
        return $table;
    }
}
