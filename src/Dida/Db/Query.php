<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db;

use \Dida\Debug\Debug;

/**
 * Query
 */
abstract class Query
{
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
     * SQL片段
     *
     * @var array
     */
    protected $clause = [
        'table'   => ['sql' => '', 'params' => []],
        'fields'  => ['sql' => '', 'params' => []],
        'join'    => ['sql' => '', 'params' => []],
        'where'   => ['sql' => '', 'params' => []],
        'groupby' => ['sql' => '', 'params' => []],
        'having'  => ['sql' => '', 'params' => []],
        'orderby' => ['sql' => '', 'params' => []],
        'limit'   => ['sql' => '', 'params' => []],
        'offset'  => ['sql' => '', 'params' => []],
        'set'     => ['sql' => '', 'params' => []],
        'values'  => ['sql' => '', 'params' => []],
    ];

    /**
     * 字段列表
     *
     * @var array
     */
    protected $_fields = [];

    /**
     * join的数据表
     *
     * [
     *   [join类型, 表, 别名, ON条件, 参数数组],
     * ]
     *
     * @var array
     */
    protected $_joinItems = [];

    /**
     * WHERE的items
     */
    protected $_whereItems = [];

    /**
     * WHERE的逻辑运算，默认为AND
     *
     * @var string
     */
    protected $_whereLogic = "AND";

    /**
     * GROUP BY
     *
     * @var array
     */
    protected $_groupby = [];

    /**
     * HAVING的items
     *
     * @var array
     */
    protected $_havingItems = [];

    /**
     * HAVING的逻辑运算，默认为AND
     *
     * @var string
     */
    protected $_havingLogic = "AND";

    /**
     * ORDER BY
     *
     * @var array
     */
    protected $_orderby = [];

    /**
     * LIMIT
     *
     * @var int|string
     */
    protected $_limit = null;

    /**
     * OFFSET
     *
     * @var int
     */
    protected $_offset = null;

    /**
     * 要UPDATE/INSERT的数据行
     *
     * @var array
     */
    protected $_record = [];

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
            // 如果prefix为null，则从配置文件中读取prefix
            $this->tablePrefix = $driver->conf["prefix"];
        } else {
            // 如果prefix不为null，则使用此prefix
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
     * 构建 SQL
     *
     * @param string $tpl   SQL模板
     * @param array  $parts 从句名称
     *
     * @return array 生成的结果
     *               ['sql'=>..., 'params'=>[...]]
     */
    protected function buildSQL($tpl, array $parts)
    {
        $sql = [$tpl];
        $params = [];
        foreach ($parts as $part) {
            switch ($part) {
                case 'table':
                    $this->clause["table"] = $this->clauseTABLE();
                    break;
                case 'fields':
                    $this->clause["fields"] = $this->clauseFIELDS();
                    break;
                case 'join':
                    $this->clause["join"] = $this->clauseJOIN();
                    break;
                case 'where':
                    $this->clause["where"] = $this->clauseWHERE();
                    break;
                case 'groupby':
                    $this->clause["groupby"] = $this->clauseGROUPBY();
                    break;
                case 'having':
                    $this->clause["having"] = $this->clauseHAVING();
                    break;
                case 'orderby':
                    $this->clause["orderby"] = $this->clauseORDERBY();
                    break;
                case 'offset':
                    $this->clause["offset"] = $this->clauseOFFSET();
                    break;
                case 'limit':
                    $this->clause["limit"] = $this->clauseLIMIT();
                    break;
                case 'set':
                    $this->clause["set"] = $this->clauseSET();
                    break;
                case 'values':
                    $this->clause["values"] = $this->clauseVALUES();
                    break;
                default:
                    throw new \Exception("Unknown SQL part '$part' in " . __METHOD__);
            }
            $sql[] = $this->clause[$part]['sql'];
            $params = array_merge($params, $this->clause[$part]['params']);
        }
        $sql = call_user_func_array('sprintf', $sql);
        return [
            'sql'    => $sql,
            'params' => $params,
        ];
    }

    /**
     * 构建 SELECT 语句
     *
     * @return array 生成的结果
     *               ['sql'=>..., 'params'=>[...]]
     */
    protected function buildSELECT()
    {
        $tpl = 'SELECT %s FROM %s %s %s %s %s %s %s';
        $parts = [
            'fields',
            'table',
            'join',
            'where',
            'groupby',
            'having',
            'orderby',
            'offset',
            'limit',
        ];
        return $this->buildSQL($tpl, $parts);
    }

    /**
     * 构建 COUNT 语句
     *
     * @return array 生成的结果
     *               ['sql'=>..., 'params'=>[...]]
     */
    protected function buildCOUNT()
    {
        $tpl = 'SELECT count(%s) FROM %s %s %s %s %s %s %s';
        $parts = [
            'fields',
            'table',
            'join',
            'where',
            'groupby',
            'having',
            'orderby',
            'offset',
            'limit',
        ];
        return $this->buildSQL($tpl, $parts);
    }

    /**
     * 构建 INSERT 语句
     *
     * @return array 生成的结果
     *               ['sql'=>..., 'params'=>[...]]
     */
    protected function buildINSERT()
    {
        $tpl = 'INSERT %s %s';
        $parts = [
            'table',
            'values',
        ];
        return $this->buildSQL($tpl, $parts);
    }

    /**
     * 构建 UPDATE 语句
     *
     * @return array 生成的结果
     *               ['sql'=>..., 'params'=>[...]]
     */
    protected function buildUPDATE()
    {
        $tpl = 'UPDATE %s SET %s %s %s %s %s %s';
        $parts = [
            'table',
            'set',
            'join',
            'where',
            'groupby',
            'having',
            'orderby',
        ];
        return $this->buildSQL($tpl, $parts);
    }

    /**
     * 构建 DELETE 语句
     *
     * @return array 生成的结果
     *               ['sql'=>..., 'params'=>[...]]
     */
    protected function buildDELETE()
    {
        $tpl = 'DELETE %s %s %s';
        $parts = [
            'table',
            'join',
            'where',
        ];
        return $this->buildSQL($tpl, $parts);
    }

    /**
     * 返回 TABLE 子句
     *
     * @return array 生成的结果 ['sql'=>..., 'params'=>[...]]
     */
    protected function clauseTABLE()
    {
        return [
            'sql'    => $this->sqlMainTable(),
            'params' => [],
        ];
    }

    /**
     * 返回 FIELDS 子句
     *
     * @return array 生成的结果 ['sql'=>..., 'params'=>[...]]
     */
    protected function clauseFIELDS()
    {
        // 如果$_fields为[]，直接返回
        if (!$this->_fields) {
            return [
                'sql'    => '*',
                'params' => [],
            ];
        }

        // 拼装
        // 如果是关联数组，加上AS部分，key是别名
        // 如果是索引数组，则没有AS部分
        $r = [];
        foreach ($this->_fields as $key => $field) {
            if (is_int($key)) {
                $r[] = $field;
            } elseif ($key === $field) {
                $r[] = $field;
            } else {
                $r[] = $field . ' AS ' . $this->quoteIdentifer($key);
            }
        }

        // 返回
        return [
            'sql'    => implode(', ', $r),
            'params' => [],
        ];
    }

    /**
     * 返回 JOIN 子句
     *
     * @return array 生成的结果 ['sql'=>..., 'params'=>[...]]
     */
    protected function clauseJOIN()
    {
        $sql = [];
        $params = [];

        foreach ($this->_joinItems as $join) {
            list($_jointype, $_table, $_as, $_on, $_params) = $join;

            // _table
            $_table = $this->quoteIdentifier($this->tablePrefix . $_table);

            // _as
            if ($_as) {
                $_as = ' AS ' . $this->quoteIdentifier($_as);
            }

            // _on
            $sql[] = "\n$_jointype $_table{$_as} ON $_on";

            // _params
            if ($_params) {
                $params = array_merge($params, $_params);
            }
        }

        // 返回
        return [
            'sql'    => implode('', $sql),
            'params' => $params,
        ];
    }

    /**
     * 返回 WHERE 子句
     *
     * @return array 生成的结果 ['sql'=>..., 'params'=>[...]]
     */
    protected function clauseWHERE()
    {
        // 如果$_whereItems为空
        if (!$this->_whereItems) {
            return [
                'sql'    => '',
                'params' => [],
            ];
        }

        // 合并items
        $item = $this->combineWHERE();

        // 加上 WHERE
        $item['sql'] = "\nWHERE " . $item['sql'];

        // 返回
        return $item;
    }

    /**
     * 返回 GROUP BY 子句
     *
     * @return array 生成的结果 ['sql'=>..., 'params'=>[...]]
     */
    protected function clauseGROUPBY()
    {
        // 如果没有设置 GROUP BY，直接返回
        if ($this->_groupby) {
            $s = "\nGROUP BY " . implode(', ', $this->_groupby);
        } else {
            $s = '';
        }

        // 返回
        return [
            'sql'    => $s,
            'params' => [],
        ];
    }

    /**
     * 返回 HAVING 子句
     *
     * @return array 生成的结果 ['sql'=>..., 'params'=>[...]]
     */
    protected function clauseHAVING()
    {
        // 如果$_havingItems为空，直接返回
        if (!$this->_havingItems) {
            return [
                'sql'    => '',
                'params' => [],
            ];
        }

        // 合并items
        $item = $this->combineHAVING();

        // 加上 HAVING
        $item['sql'] = "\nHAVING " . $item['sql'];

        // 返回
        return $item;
    }

    /**
     * 返回 ORDER BY 子句
     *
     * @return array ['sql'=>..., 'params'=>[...]]
     */
    protected function clauseORDERBY()
    {
        //
        if ($this->_orderby) {
            $s = "\nORDER BY " . implode(', ', $this->_orderby);
        } else {
            $s = '';
        }

        // 返回
        return [
            'sql'    => $s,
            'params' => [],
        ];
    }

    /**
     * 返回 LIMIT 子句
     *
     * @return array ['sql'=>..., 'params'=>[...]]
     */
    protected function clauseLIMIT()
    {
        if ($this->_limit) {
            $s = "\nLIMIT " . $this->_limit;
        } else {
            $s = '';
        }

        // 返回
        return [
            'sql'    => $s,
            'params' => [],
        ];
    }

    /**
     * 返回 OFFSET 子句
     *
     * @return array ['sql'=>..., 'params'=>[...]]
     */
    protected function clauseOFFSET()
    {
        // 构造
        if ($this->_offset) {
            $s = " OFFSET " . $this->_offset;
        } else {
            $s = '';
        }

        // 返回
        return [
            'sql'    => $s,
            'params' => [],
        ];
    }

    /**
     * SET 子句
     *
     * @param array $row 数据
     *
     * @return array ['sql'=>..., 'params'=>[...]]
     */
    protected function clauseSET()
    {
        // 如果_record为[]，直接抛异常
        if (!$this->_record) {
            throw new \Exception("The record for UPDATE is not set.");
        }

        // 生成
        $sql = [];
        $params = [];
        foreach ($this->_record as $field => $value) {
            $sql[] = $this->quoteIdentifier($field) . '=?';
            $params[] = $value;
        }

        // 返回
        return [
            'sql'    => "\nSET " . implode(', ', $sql),
            'params' => $params,
        ];
    }

    /**
     * VALUES 子句
     *
     * @param array $row 数据
     *
     * @return array ['sql'=>..., 'params'=>[...]]
     */
    protected function clauseVALUES()
    {
        // 如果_record为[]，直接抛异常
        if (!$this->_record) {
            throw new \Exception("The record for INSERT is not set.");
        }

        // 初始化
        $fields = [];
        $marks = [];
        $params = [];

        // 生成
        foreach ($this->_record as $field => $value) {
            $fields[] = $this->quoteIdentifier($field);
            $marks[] = '?';
            $params[] = $value;
        }

        // 处理
        $fields = implode(', ', $fields);
        $marks = implode(',', $marks);

        // 返回
        return [
            'sql'    => "($fields) VALUES ($marks)",
            'params' => $params,
        ];
    }

    /**
     * 返回主表的SQL表达式
     *
     * @return string
     */
    protected function sqlMainTable()
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

    /**
     * 合并_whereItems
     *
     * @return array ['sql'=>..., 'params'=>[...]]
     */
    protected function combineWHERE()
    {
        // 如果_whereItems为空，返回空数组
        if (!$this->_whereItems) {
            return [];
        }

        // 如果_whereItems只有1个，无需处理，直接返回这个item
        if (count($this->_whereItems) === 1) {
            return $this->_whereItems[0];
        }

        // sql
        $sql = array_column($this->_whereItems, 'sql');
        $sql = implode(" {$this->_whereLogic} ", $sql);

        // params
        $params = [];
        foreach ($this->_whereItems as $item) {
            $params = array_merge($params, $item["params"]);
        }

        // 返回
        return [
            'sql'    => "($sql)",
            'params' => $params,
        ];
    }

    /**
     * 合并_havingItems
     *
     * @return array ['sql'=>..., 'params'=>[...]]
     */
    protected function combineHAVING()
    {
        // 如果_havingItems为空，返回空数组
        if (!$this->_havingItems) {
            return [];
        }

        // 如果_havingItems只有1个，无需处理，直接返回这个item
        if (count($this->_havingItems) === 1) {
            return $this->_havingItems[0];
        }

        // sql
        $sql = array_column($this->_havingItems, 'sql');
        $sql = implode(" {$this->_havingLogic} ", $sql);

        // params
        $params = [];
        foreach ($this->_havingItems as $item) {
            $params = array_merge($params, $item["params"]);
        }

        // 返回
        return [
            'sql'    => "($sql)",
            'params' => $params,
        ];
    }

    /**
     * 生成N个形参?
     *
     * @param int $num
     *
     * @return string
     */
    protected function makeMarks($num)
    {
        $m = str_repeat('?,', $num);
        return substr($m, 0, -1);
    }

    /**
     * SELECT的输出字段
     *
     * @param string|array $fields
     *
     * @return \Dida\Db\Query $this
     */
    public function fields($fields)
    {
        // 如果为空，直接忽略
        if (!$fields) {
            return $this;
        }

        // 如果是字符串，直接保存
        if (is_string($fields)) {
            $this->_fields[] = $fields;
            return $this;
        }

        // 如果是数组
        if (is_array($fields)) {
            $this->_fields = array_merge($this->_fields, $fields);
            return $this;
        }
    }

    /**
     * 强制设置Query的查询字段fields
     *
     * @param array $fields
     *
     * @return \Dida\Db\Query $this
     */
    public function initFields(array $fields = [])
    {
        $this->_fields = $fields;
        return $this;
    }

    /**
     * JOIN
     *
     * @param string $table
     * @param string $as
     * @param string $on
     * @param array  $params
     *
     * @return \Dida\Db\Query $this
     */
    public function join($table, $as, $on, array $params = [])
    {
        $this->_joinItems[] = ["JOIN", $table, $as, $on, $params];
        return $this;
    }

    /**
     * INNER JOIN
     *
     * @param string $table
     * @param string $as
     * @param string $on
     * @param array  $params
     *
     * @return \Dida\Db\Query $this
     */
    public function innerJoin($table, $as, $on, array $params = [])
    {
        $this->_joinItems[] = ["INNER JOIN", $table, $as, $on, $params];
        return $this;
    }

    /**
     * LEFT JOIN
     *
     * @param string $table
     * @param string $as
     * @param string $on
     * @param array  $params
     *
     * @return \Dida\Db\Query $this
     */
    public function leftJoin($table, $as, $on, array $params = [])
    {
        $this->_joinItems[] = ["LEFT JOIN", $table, $as, $on, $params];
        return $this;
    }

    /**
     * RIGHT JOIN
     *
     * @param string $table
     * @param string $as
     * @param string $on
     * @param array  $params
     *
     * @return \Dida\Db\Query $this
     */
    public function rightJoin($table, $as, $on, array $params = [])
    {
        $this->_joinItems[] = ["RIGHT JOIN", $table, $as, $on, $params];
        return $this;
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
                    $this->_whereItems[] = $item;
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
        $this->_whereItems[] = $item;

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
        $this->_whereItems[] = $item;

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
        $this->_whereItems[] = [
            'sql'    => $sql,
            'params' => $params,
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
        // 如果_whereItems不为空，先把前面设置的所有WHERE做一个打包
        if ($this->_whereItems) {
            $item = $this->combineWHERE();
            $this->_whereItems = [$item];
        }

        // 设置新WHERE逻辑
        $this->_whereLogic = $logic;

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
        $this->_whereLogic('OR');
        return $this;
    }

    /**
     * 设置 $this->wherLogic = 'AND'
     *
     * @return \Dida\Db\Query $this
     */
    public function whereAnd()
    {
        $this->_whereLogic('AND');
        return $this;
    }

    /**
     * 设置 GROUP BY 字段
     *
     * @param string|array $fields
     *
     * @return \Dida\Db\Query $this
     */
    public function groupby($fields)
    {
        if (is_string($fields)) {
            $this->_groupby[] = $fields;
            return $this;
        } elseif (is_array($fields)) {
            $this->_groupby = array_merge($this->_groupby, $fields);
            return $this;
        }

        throw new \Exception("Invalid parameter type.");
    }

    /**
     * having条件
     *
     * 方式1： having($field, $op, $value)
     * 方式2： having(array $array)
     *              $array 关联数组。 field => value
     *
     * @param string|array $field 字段名，或者名字对
     * @param string       $op    运算符
     * @param mixed        $value 值
     *
     * @return \Dida\Db\Query $this
     */
    public function having($field, $op = '=', $value = null)
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
                    $this->havingIn($field, $value);
                    break;
                case "BETWEEN":
                    $value1 = $value[0];
                    $value2 = $value[1];
                    $this->havingBetween($field, $value1, $value2);
                    break;
                default:
                    $field = $this->quoteIdentifier($field);
                    $item = [
                        'sql'    => "($field $op ?)",
                        'params' => [$value],
                    ];
                    $this->_havingItems[] = $item;
            }

            // 完成
            return $this;
        }

        // 如果$field是数组
        if (is_array($field)) {
            foreach ($field as $f => $v) {
                $this->having($f, $op, $v);
            }

            // 完成
            return $this;
        }

        // 其它情况抛异常
        throw new \Exception('Invalid parameter in ' . __METHOD__ . '()');
    }

    /**
     * HAVING: IN
     *
     * @param string $field  字段
     * @param mixed  $values 值
     *
     * @return \Dida\Db\Query $this
     */
    public function havingIn($field, array $values)
    {
        $field = $this->quoteIdentifier($field);
        $marks = substr(str_repeat('?,'), 0, -1);
        $item = [
            'sql'    => "($field IN ($marks))",
            'params' => [$value1, $value2],
        ];
        $this->_havingItems[] = $item;

        // 完成
        return $this;
    }

    /**
     * HAVING: BETWEEN
     *
     * @param string $field  字段
     * @param mixed  $value1 值
     * @param mixed  $value2 值
     *
     * @return \Dida\Db\Query $this
     */
    public function havingBetween($field, $value1, $value2)
    {
        $field = $this->quoteIdentifier($field);
        $item = [
            'sql'    => "($field BETWEEN ? AND ?)",
            'params' => [$value1, $value2],
        ];
        $this->_havingItems[] = $item;

        // 完成
        return $this;
    }

    /**
     * HAVING: 对于复杂HAVING表达式，提供这个函数直接设置
     *
     * @param string $sql    SQL
     * @param array  $params 参数
     *
     * @return \Dida\Db\Query $this
     */
    public function havingRaw($sql, array $params)
    {
        $this->_havingItems[] = [
            'sql'    => $sql,
            'params' => $params,
        ];

        // 完成
        return $this;
    }

    /**
     * 设置 $this->wherLogic
     *
     * @return \Dida\Db\Query $this
     */
    public function havingLogic($logic)
    {
        // 如果_havingItems不为空，先把前面设置的所有HAVING做一个打包
        if ($this->_havingItems) {
            $item = $this->combineHAVING();
            $this->_havingItems = [$item];
        }

        // 设置新HAVING逻辑
        $this->_havingLogic = $logic;

        // 完成
        return $this;
    }

    /**
     * 设置 $this->wherLogic = 'OR'
     *
     * @return \Dida\Db\Query $this
     */
    public function havingOr()
    {
        $this->_havingLogic('OR');
        return $this;
    }

    /**
     * 设置 $this->wherLogic = 'AND'
     *
     * @return \Dida\Db\Query $this
     */
    public function havingAnd()
    {
        $this->_havingLogic('AND');
        return $this;
    }

    /**
     * ORDER BY
     *
     * @param string $field
     * @param string $order 值可为'','ASC','DESC'
     *
     * @return \Dida\Db\Query $this
     */
    public function orderby($field, $order = '')
    {
        if ($order) {
            $this->_orderby[] = "$field $order";
        } else {
            $this->_orderby[] = $field;
        }

        return $this;
    }

    /**
     * LIMIT
     *
     * LIMIT offset,limit 等于 LIMIT limit OFFSET offset
     *
     * @param int|string $limit
     */
    public function limit($limit)
    {
        $this->_limit = $limit;
    }

    /**
     * @param int $offset
     */
    public function offset($offset)
    {
        $this->_offset = $offset;
    }

    /**
     * INSERT操作
     *
     * @param array $row 要插入的行数据
     *
     * @return \Dida\Db\ResultSet
     */
    public function insert(array $row)
    {
        // 参数处理
        $this->_record = $row;

        // build
        $sp = $this->buildINSERT();

        // 执行
        $rs = $this->driver->execWrite($sp['sql'], $sp['params']);

        // 返回 Dida\Db\ResultSet
        return $rs;
    }

    /**
     * UPDATE操作
     *
     * @param array $row 要更新的数据项
     *
     * @return \Dida\Db\ResultSet
     */
    public function update(array $row)
    {
        // 参数处理
        $this->_record = $row;

        // build
        $sp = $this->buildUPDATE();

        // 执行
        $rs = $this->driver->execWrite($sp['sql'], $sp['params']);

        // 返回 Dida\Db\ResultSet
        return $rs;
    }

    /**
     * DELETE
     *
     * @return \Dida\Db\ResultSet
     */
    public function delete()
    {
        // build
        $sp = $this->buildDELETE();

        // 执行
        $rs = $this->driver->execWrite($sp['sql'], $sp['params']);

        // 返回 Dida\Db\ResultSet
        return $rs;
    }

    /**
     * SELECT操作
     *
     * @param array|string $fields 字段列表
     * @param array|string $where  条件。参见 $this->clauseWHERE()。
     * @param string       $limit  LIMIT子句
     *
     * @return \Dida\Db\ResultSet
     */
    public function select($fields = '', $where = '', $limit = '')
    {
        // 参数处理
        $this->fields($fields);
        $this->where($where);
        $this->limit($limit);

        // build
        $sp = $this->buildSELECT();

        // 执行
        $rs = $this->driver->execRead($sp['sql'], $sp['params']);

        // 返回 Dida\Db\ResultSet
        return $rs;
    }

    /**
     * COUNT
     *
     * @param array|string $fieldlist 字段列表。从性能原因考虑，这个参数最好设置为表的主键。
     * @param array|string $where     条件。参见 $this->clauseWHERE()。
     * @param string       $limit     LIMIT子句
     *
     * @return int|false 成功返回count，失败返回false
     */
    public function count($fields = '*', array $where = [])
    {
        // 参数处理
        $this->fields($fields);
        $this->where($where);
        $this->limit($limit);

        // build
        $sp = $this->buildCOUNT();

        // 执行
        $rs = $this->driver->execRead($sp['sql'], $sp['params']);

        // 如果出错，返回false
        if ($rs->fail()) {
            return false;
        }

        // 行数据
        $row = $rs->fetchByNum();
        if ($row === false) {
            return false;
        }

        // 成功，返回count
        return intval($row[0]);
    }

    /**
     * 分页显示
     *
     * @param int         $page
     * @param int         $pagesize
     * @param string|null $uniquekey 主键或者唯一键，以便优化总数查询
     *
     * @return array|false 成功返回如下数组结构，失败返回false
     *
     * [
     *     'page'     => 1,    // 当前页
     *     'pagesize' => 10,   // 每页显示条数
     *     'count'    => 99,   // 记录总个数
     *     'pages'    => 10,   // 总页数
     *     'prevpage' => null, // 上一页。无上页则为null
     *     'nextpage' => 2,    // 下一页。无下页则为null
     *     'data'     => [[...], [...], ...], // 本页的记录
     * ]
     */
    public function pager($page = 1, $pagesize = 10, $uniquekey = null)
    {
        // 如果参数不合法，直接返回false
        if ($page < 1 || $pagesize < 1) {
            return false;
        }

        // 暂存$this->_fields
        $tempfields = $this->_fields;

        // 如果设置了 $uniquekey
        if (is_string($uniquekey)) {
            $this->fields($uniquekey);
        }

        // build
        $sp = $this->buildCOUNT();

        // 执行
        $rs = $this->driver->execRead($sp['sql'], $sp['params']);

        // 如果出错，返回false
        if ($rs->fail()) {
            return false;
        }

        // 成功，返回记录总条数
        $count = intval($rs->getColumn("count"));

        // 总页数
        $pages = ceil($count / $pagesize);

        // 恢复$this->_fields
        $this->_fields = $tempfields;

        // 设置分页
        $from = ($page - 1) * $pagesize;
        $limit = $from . ',' . $pagesize;
        $this->limit($limit);

        $sp = $this->buildSELECT();

        // 执行
        $rs = $this->driver->execRead($sp['sql'], $sp['params']);

        // 如果出错，返回false
        if ($rs->fail()) {
            return false;
        }

        // 生成data
        $data = $rs->getRows();
        $datacount = count($data);

        // 上一页
        if ($page > 1) {
            $prevpage = $page - 1;
        } else {
            $prevpage = null; // 没有上一页
        }

        // 下一页
        if (($from + $datacount) < $count) {
            $nextpage = $page + 1;
        } else {
            $nextpage = null; // 没有下一页
        }

        // 返回
        return [
            'page'     => $page,
            'pagesize' => $pagesize,
            'count'    => $count,
            'pages'    => $pages,
            'prevpage' => $prevpage,
            'nextpage' => $nextpage,
            'data'     => $data,
        ];
    }
}
