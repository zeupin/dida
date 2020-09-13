<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db;

/**
 * Mysql数据表操作
 */
class MysqlTable extends \Dida\Db\Table
{
    /**
     * 版本号
     */
    const VERSION = '20200913';

    /**
     * @var string Mysql的定界符是"`"
     */
    protected $borderchar = "`";
}
