<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Mysql;

use \PDO;
use \Dida\Util\ArrayEx;

class SchemaInfo extends \Dida\Db\SchemaInfo
{
    /**
     * 版本号
     */
    const VERSION = '20200909';

    /**
     * 返回所有schemas的名字
     */
    public function getSchemaNames()
    {
        $sql = <<<SQL
SELECT
    `SCHEMA_NAME` AS `schema`
FROM
    `information_schema`.`SCHEMATA`
WHERE
    `SCHEMA_NAME` NOT IN ('mysql', 'information_schema')
SQL;
        $params = [];
        $sth = $this->pdo->prepare($sql);
        $sth->execute($params);
        $rows = $sth->fetchAll(PDO::FETCH_COLUMN);
        return $rows;
    }

    /**
     * 返回所有schemas的元信息
     *
     * @return array
     */
    public function getSchemas()
    {
        $sql = <<<SQL
SELECT
    `SCHEMA_NAME` AS `schema`,
    `DEFAULT_CHARACTER_SET_NAME` AS `charset`,
    `DEFAULT_COLLATION_NAME` AS `collation`
FROM
    `information_schema`.`SCHEMATA`
WHERE
    `SCHEMA_NAME` NOT IN ('mysql', 'information_schema')
SQL;
        $params = [];
        $sth = $this->pdo->prepare($sql);
        $sth->execute($params);
        $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
        $rows = ArrayEx::addKeys($rows, 'schema');
        return $rows;
    }

    /**
     * 返回指定schema的所有数据表的名字
     *
     * @param string $schema 指定的schema名
     * @param string $prefix 数据表前缀
     *
     * @return array
     */
    public function getTableNames($schema, $prefix = '')
    {
        $sql = <<<SQL
SELECT
    `TABLE_NAME` AS `table`
FROM
    `information_schema`.`TABLES`
WHERE
    `TABLE_SCHEMA` LIKE ?
    AND `TABLE_NAME` LIKE ?
SQL;
        $params = [$schema, "$prefix%"];
        $sth = $this->pdo->prepare($sql);
        $sth->execute($params);
        $rows = $sth->fetchAll(PDO::FETCH_COLUMN);
        return $rows;
    }

    /**
     * 返回指定schema的所有数据表的元信息
     *
     * @param string $schema 指定的schema名
     * @param string $prefix 数据表前缀
     *
     * @return array
     */
    public function getTables($schema, $prefix = '')
    {
        $sql = <<<SQL
SELECT
    `TABLE_SCHEMA` AS `schema`,
    `TABLE_NAME` AS `table`,
    `TABLE_TYPE` AS `type`,
    `TABLE_COMMENT` AS `comment`,
    `TABLE_COLLATION` AS `collation`,
    `ENGINE` AS `engine`
FROM
    `information_schema`.`TABLES`
WHERE
    `TABLE_SCHEMA` LIKE ?
    AND `TABLE_NAME` LIKE ?
SQL;
        $params = [$schema, "$prefix%"];
        $sth = $this->pdo->prepare($sql);
        $sth->execute($params);
        $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
        $rows = ArrayEx::addKeys($rows, 'table');
        return $rows;
    }

    /**
     * 返回指定table的所有字段的名字
     *
     * @param string $schema 指定的schema名
     * @param string $table  指定的table名
     *
     * @return array
     */
    public function getColumnNames($schema, $table)
    {
        $sql = <<<SQL
SELECT
    `COLUMN_NAME` AS `column`
FROM
    `information_schema`.`COLUMNS`
WHERE
    (`TABLE_SCHEMA` LIKE ?) AND (`TABLE_NAME` LIKE ?)
SQL;
        $params = [$schema, $table];
        $sth = $this->pdo->prepare($sql);
        $sth->execute($params);
        $rows = $sth->fetchAll(PDO::FETCH_COLUMN);
        return $rows;
    }

    /**
     * 返回指定table的所有字段的元信息
     *
     * @param string $schema 指定的schema名
     * @param string $table  指定的table名
     *
     * @return array
     */
    public function getColumns($schema, $table)
    {
        $sql = <<<SQL
SELECT
    `TABLE_SCHEMA` AS `schema`,
    `TABLE_NAME` AS `table`,
    `COLUMN_NAME` AS `column`,
    `DATA_TYPE` AS `datatype`,
    `COLUMN_DEFAULT` AS `default`,
    `IS_NULLABLE` AS `nullable`,
    `CHARACTER_MAXIMUM_LENGTH` AS `size`,
    `NUMERIC_PRECISION` AS `precision`,
    `NUMERIC_SCALE` AS `scale`,
    `CHARACTER_SET_NAME` AS `charset`,
    `COLLATION_NAME` AS `collation`,
    `DATETIME_PRECISION` AS `data_precision`,
    `COLUMN_TYPE` AS `column_type`,
    `COLUMN_KEY` AS `column_key`,
    `EXTRA` AS `extra`,
    `COLUMN_COMMENT` AS `comment`
FROM
    `information_schema`.`COLUMNS`
WHERE
    (`TABLE_SCHEMA` LIKE ?) AND (`TABLE_NAME` LIKE ?)
ORDER BY
    `ORDINAL_POSITION`
SQL;
        $params = [$schema, $table];
        $sth = $this->pdo->prepare($sql);
        $sth->execute($params);
        $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
        $rows = ArrayEx::addKeys($rows, 'column');
        return $rows;
    }
}
