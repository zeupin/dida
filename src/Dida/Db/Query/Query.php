<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Query;

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
     */
    public function __construct($driver, $name, $prefix, $as)
    {
        $this->driver = $driver;
        $this->mainTable = $prefix . $name;
        $this->tablePrefix = $prefix;
        $this->mainTableAs = $as;

        // 设置标识符引用字符
        $this->setIdentifierQuote();
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

        // 如果名称中已有转义符，则不转义
        if (strpos($identifier, $this->left_quote) !== false || strpos($identifier, $this->right_quote) !== false) {
            return $identifier;
        }

        // 开始转义
        $a = explode('.', $identifier);
        foreach ($a as &$i) {
            $i = $this->left_quote . $i . $this->right_quote;
        }
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
