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
     * Traits
     */
    use WhereTrait;
    use JoinTrait;
    use ActionTrait;

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
     * join的数据表
     *
     * [
     *   [join类型, 表, 别名, [on条件1, on条件2, ...]],
     * ]
     *
     * @var array
     */
    protected $joins = [];

    /**
     * __construct
     */
    public function __construct($driver, $name, $prefix, $as)
    {
        $this->tablePrefix = $prefix;
        $this->mainTable = $prefix . $name;
        $this->driver = $driver;

        // 设置标识符引用字符
        $this->setIdentifierQuote();
    }

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
     * WHERE子句
     *
     * $where为空串: 不要WHERE子句
     * $where为字符串: 输出 WHERE $where
     * $where为关联数组: $key=$value，并用AND连接
     * $where为序列数组：（功能预留，可以做更复杂的处理）
     *
     * 如果为复杂条件，建议用字符串形式。
     *
     * @param array|string $where 条件
     *
     * @return array|false 成功返回array，失败返回false
     */
    protected function clauseWHERE($where)
    {
        // 如果where为字符串
        if (is_string($where)) {
            if (trim($where) === '') {
                $ret = [
                    'sql'    => '',
                    'params' => [],
                ];
                return $ret;
            } else {
                $ret = [
                    'sql'    => "WHERE $where",
                    'params' => [],
                ];
                return $ret;
            }
        }

        // 如果$where为数组
        if (is_array($where)) {
            // 如果为[]空数组，表示不需要WHERE子句
            if (!$where) {
                $ret = [
                    'sql'    => '',
                    'params' => [],
                ];
                return $ret;
            }

            // 取$where的第一个key。
            // 根据这个key判断$where是关联数组还是序列数组，然后根据不同的类型进行处理。
            if (key($where) === 0) {
                // 如果为序列数组
                // todo 预留
                throw new \Exception('"$where" parameter shoule be an assoc array or a string.');
            } else {
                // 如果为关联数组
                $sql = [];
                $params = [];
                foreach ($where as $k => $v) {
                    $sql[] = "{$this->left_quote}$k{$this->right_quote} = ?";
                    $params[] = $v;
                }
                $ret = [
                    'sql'    => 'WHERE ' . implode(" AND ", $sql),
                    'params' => $params,
                ];
                return $ret;
            }
        }

        throw new \Exception('"$where" parameter type is invalid.');
    }

    /**
     * SET子句
     *
     * @param array $row 数据
     *
     * @return array|false 成功返回array，失败返回false
     */
    protected function clauseSET(array $row)
    {
        // 如果$row为[]，直接抛异常
        if (!$row) {
            throw new \Exception("SET clause can not be blank.");
        }

        // 生成
        $sql = [];
        $params = [];
        foreach ($row as $field => $value) {
            $sql[] = "{$this->left_quote}$field{$this->right_quote} = ?";
            $params[] = $value;
        }
        return [
            'sql'    => "SET " . implode(', ', $sql),
            'params' => $params,
        ];
    }
}
