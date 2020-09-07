<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Db;

use \Exception;
use \PDO;

class Db
{
    /**
     * 版本号
     */
    const VERSION = "20200908";

    /**
     * 配置项
     *
     * @var array
     */
    protected $conf;

    /**
     * 保存的driver实例
     *
     * @var \Dida\Db\Driver\Driver
     */
    protected $driver = null;

    /**
     * 生成的PDO实例
     *
     * @var \PDO|null|false 初始为null
     *                      init()成功，为生成的PDO实例
     *                      init()失败，则为false
     */
    public $pdo = null;

    /**
     * 开始
     *
     * @param array $conf
     */
    public function __construct(array $conf)
    {
        // 保存到本地
        $this->conf = $conf;

        // 检查driver
        if (!array_key_exists("driver", $conf)) {
            throw new Exception("Missing a required option \"driver\"", 1);
        }
    }

    /**
     * 初始化，生成PDO实例
     */
    public function init()
    {
        // 生成driver实例
        $this->driver = new $this->conf["driver"]($this->conf);

        // 生成PDO实例
        $this->pdo = $this->driver->getPDO();
    }
}
