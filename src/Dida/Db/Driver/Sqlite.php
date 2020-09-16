<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db\Driver;

class Sqlite extends Driver
{
    /**
     * 版本号
     */
    const VERSION = '20200907';

    /**
     * 返回schemainfo实例
     *
     * @return \Dida\Db\SchemaInfo|false 成功返回SchemaInfo实例，失败返回false
     *
     * @todo 待实现
     */
    public function schemainfo()
    {
        throw new \Exception("todo");
    }
}
