<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Console\Commands;

use \Dida\Console\Command;
use \Dida\Console\Console;

class ErrMsg extends Command
{
    /**
     * 没有匹配到任何路由
     */
    public function notFound()
    {
        $msg = Console::ss('Error:', Console::RED_BG . Console::WHITE) .
            "Can't match any route: " .
            Console::ss($this->args->joinParametersAndOptions(), Console::CYAN);
        Console::prln($msg);
    }
}
