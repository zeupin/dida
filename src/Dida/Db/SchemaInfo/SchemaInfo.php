<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\SchemaInfo;

use \PDO;

/**
 * 数据库的信息
 *
 * 1. getSchemaNames() 查询所有schema名
 * 2. getTableNames()  查询指定schema的所有数据表名
 */
abstract class SchemaInfo
{
    /**
     * 版本号
     */
    const VERSION = "20200909";

    /*
     * 列的基本数据类型
     */
    const COLUMN_TYPE_UNKNOWN = 'unknown';
    const COLUMN_TYPE_INT = 'int';
    const COLUMN_TYPE_FLOAT = 'float';
    const COLUMN_TYPE_STRING = 'string';
    const COLUMN_TYPE_BOOL = 'bool';
    const COLUMN_TYPE_TIME = 'time';
    const COLUMN_TYPE_ENUM = 'enum';
    const COLUMN_TYPE_SET = 'set';
    const COLUMN_TYPE_RESOURCE = 'res';
    const COLUMN_TYPE_STREAM = 'stream';

    /**
     * PDO实例
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * 初始化
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * 返回所有schemas的名字
     *
     * @return array
     */
    abstract public function getSchemaNames();

    /**
     * 返回所有schemas的元信息
     *
     * @return array
     *
     * 必填字段：
     *     schema  数据库名
     */
    abstract public function getSchemas();

    /**
     * 返回指定schema的所有数据表的名字
     *
     * @param string $schema 指定的schema
     *
     * @return array 一维数组
     *
     * 必填字段：
     *     schema  数据库名
     *     table   数据表名
     */
    abstract public function getTableNames($schema);

    /**
     * 返回指定schema的所有数据表的元信息
     *
     * @param string $schema 指定的schema
     *
     * @return array 二维数组
     *
     * 必填字段：
     *     schema  数据库名
     *     table   数据表名
     */
    abstract public function getTables($schema);

    /**
     * 返回指定table的所有字段的名字
     *
     * @param string $schema 指定的schema
     * @param string $table  指定的table
     *
     * @return array 一维数组
     */
    abstract public function getColumnNames($schema, $table);

    /**
     * 返回指定table的所有字段的元信息
     *
     * @param string $schema 指定的schema
     * @param string $table  指定的table
     *
     * @return array 二维数组
     *
     * 必填字段：
     *     schema   数据库名
     *     table    数据表名
     *     column   数据列名
     *     datatype 列的数据类型
     *     default  缺省值
     *     nullable 是否可为null
     *     size     最大长度
     */
    abstract public function getColumns($schema, $table);
}
