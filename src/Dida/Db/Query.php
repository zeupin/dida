<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db;

use \Dida\Db\Query\WhereTrait;
use \Dida\Db\Query\JoinTrait;
use \Dida\Db\Query\InsertTrait;
use \Dida\Db\Query\UpdateTrait;
use \Dida\Db\Query\DeleteTrait;
use \Dida\Db\Query\SelectTrait;
use \Dida\Db\Query\AggregateTrait;

/**
 * Query
 */
abstract class Query
{
    /*
     * 构造SQL查询
     */
    use WhereTrait;
    use JoinTrait;

    /*
     * 执行：增删改查
     */
    use InsertTrait;
    use UpdateTrait;
    use DeleteTrait;
    use SelectTrait;

    /*
     * 执行：聚合函数
     */
    use AggregateTrait;

    /**
     * 版本号
     */
    const VERSION = '20200916';

    /**
     * @var \Dida\Db\Driver\Driver
     */
    protected $driver;

    /**
     * 标识引用符，左
     *
     * @var string
     *
     * @see setIdentifierQuote()
     */
    protected $left_quote = '';

    /**
     * 标识引用符，右
     *
     * @var string
     *
     * @see setIdentifierQuote()
     */
    protected $right_quote = '';

    /**
     * 主数据表
     *
     * 一般使用$this->driver->table(...)生成
     *
     * @var string
     */
    protected $mainTable;

    /**
     * 主数据表别名
     */
    protected $mainTableAs;

    /**
     * 数据表名前缀
     *
     * @var string
     */
    protected $tablePrefix;

    /**
     * 设置标识符引用字符
     *
     * 1. 对Mysql，左右标识引用字符分别为 ``
     * 2. 对Sqlite，左右标识引用字符分别为 ""
     * 3. 对Access，左右标识引用字符分别为 []
     *
     * @param string $left_quote
     * @param string $right_quote
     *
     * @return void
     */
    abstract protected function setIdentifierQuote();

    /**
     * __construct
     *
     * @param \Dida\Db\Driver\Driver $driver
     * @param string                 $name   主表表名
     * @param string                 $as     主表别名
     * @param string|null            $prefix 表名前缀
     *
     * @return void
     */
    public function __construct($driver, $name = '', $as = '', $prefix = null)
    {
        $this->driver = $driver;

        // 调用driver实现的抽象方法，设置标识符引用字符
        $this->setIdentifierQuote();

        // 设置表名前缀
        if ($prefix === null) {
            // 如果prefix为null，则从驱动的配置中读取prefix
            $this->tablePrefix = $driver->conf["prefix"];
        } else {
            // 如果prefix不为null，则将其设置为tablePrefix
            $this->tablePrefix = $prefix;
        }

        // 如果设置了主表
        if ($name) {
            $this->setMainTable($name, $as);
        }
    }

    /**
     * 设置主表
     *
     * @param string $name
     * @param string $as
     *
     * @return Query $this
     */
    public function setMainTable($name, $as = '')
    {
        $this->mainTable = $this->tablePrefix . $name;
        $this->mainTableAs = $as;
    }

    /**
     * 为标识名加上引用符
     *
     * 1. 如果标识名中，已经含有左引用符或者右引用符，则不会进行任何转换，直接原样输出。
     *    特别注意，如果自己写了引用符，而没有成对出现，则SQL将会出错！
     *    如果输入 username，会转为 `username`
     *    如果输入 `username`，就直接原样输出 `username`
     *    如果有点运算
     * 2. 如果有"."，会分段加引用符
     *    例：t_users.username，会分段转义输出 `t_users`.`username`
     *    例：`t_users`.username，因为含有"`"，所以会原样输出 `t_users`.username
     *
     * @var string $identifier 标识符
     *
     * @return string
     */
    protected function quoteIdentifier($identifier)
    {
        // 如果无需转义
        if ($this->left_quote === '' && $this->right_quote === '') {
            return $identifier;
        }

        // 仅对单词字符转义，其它情况直接返回原字符串
        //      <---1---><------2-------->
        $r = "/^[a-zA-Z_][a-zA-Z0-9_.]{1,}$/";
        if (!preg_match($r, $identifier)) {
            return $identifier;
        };

        // 开始转义
        $a = explode('.', $identifier);
        foreach ($a as &$i) {
            $i = $this->left_quote . $i . $this->right_quote;
        }

        // 返回
        return implode('.', $a);
    }

    /**
     * 返回主表的SQL表达式
     *
     * @return string
     */
    public function sqlMainTable()
    {
        $table = $this->quoteIdentifier($this->mainTable);
        $as = $this->quoteIdentifier($this->mainTableAs);

        if ($this->mainTableAs) {
            $sql = "$table AS $as";
        } else {
            $sql = $table;
        }

        return $sql;
    }
}
