<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

require __DIR__ . '/../_base.php';

use PHPUnit\Framework\TestCase;
use Dida\Make\DocComment;

class DocCommentTest extends TestCase
{
    /**
     * 解除某个事件上挂接的某个或者全部回调函数。
     * 如果不指定id，则表示解除此事件上的所有回调函数。
     * a * b = 3  <--这里的*不应该被替换
     *
     * @param string      $event           事件名称
     * @param string|null $id              回调函数的id
     * @param string      $no_param_remark
     *
     * @return void
     */
    public function test1()
    {
        // mock
        $mock = <<<COMMENT
    /**
     * 解除某个事件上挂接的某个或者全部回调函数。
     * 如果不指定id，则表示解除此事件上的所有回调函数。
     * a * b = 3  <--这里的*不应该被替换
     *
     * @param string      \$event           事件名称
     * @param string|null \$id              回调函数的id
     * @param string      \$no_param_remark
     *
     * @return void
     */
COMMENT;

        $dc = new DocComment($mock);

        $expected = <<<TEXT
解除某个事件上挂接的某个或者全部回调函数。
如果不指定id，则表示解除此事件上的所有回调函数。
a * b = 3  <--这里的*不应该被替换

@param string      \$event           事件名称
@param string|null \$id              回调函数的id
@param string      \$no_param_remark

@return void
TEXT;
        $debug = $dc->getDocCommentText($mock);
        $this->assertEquals($expected, $debug);

        $nodes = $dc->parseDocCommentText($debug);
        $expected = [
            [
                'tag'  => '',
                'desc' => '解除某个事件上挂接的某个或者全部回调函数。
如果不指定id，则表示解除此事件上的所有回调函数。
a * b = 3  <--这里的*不应该被替换',
            ],
            [
                'tag'  => 'param',
                'type' => 'string',
                'name' => '$event',
                'desc' => '事件名称',
            ],
            [
                'tag'  => 'param',
                'type' => 'string|null',
                'name' => '$id',
                'desc' => '回调函数的id',
            ],
            [
                'tag'  => 'param',
                'type' => 'string',
                'name' => '$no_param_remark',
                'desc' => '',
            ],
            [
                'tag'  => 'return',
                'type' => 'void',
                'desc' => '',
            ],
        ];
        // var_dump($nodes, $expected);
        $this->assertEquals($expected, $nodes);
    }
}
