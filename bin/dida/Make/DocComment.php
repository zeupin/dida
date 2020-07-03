<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Make;

/**
 * DocComment 文档注释
 */
class DocComment
{
    /**
     * 解析docComment得到的文本
     *
     * @var string
     */
    protected $text = '';

    /**
     * 解析成功后得到的节点
     *
     * @var array
     */
    protected $nodes = [];

    /**
     * 初始化
     *
     * @param string $docComment
     *
     * @return void
     */
    public function __construct($docComment)
    {
        $this->text = $this->getDocCommentText($docComment);
        $this->nodes = $this->parseDocCommentText($this->text);
    }

    /**
     * 删除一个文档注释的PHP注释标记
     *
     * 1. 去除 PHPDoc 的 /** 和 *\/
     * 2. 去除 PHPDoc 每行开头的 * (如果有的话)
     * 3. 把换行符统一为 \n
     *
     * @param string $doccomment
     *
     * @return string 处理后的结果
     */
    public function getDocCommentText($doccomment)
    {
        // 统一换行符
        $text = preg_replace("/\r\n/", "\n", $doccomment);

        // 删除PHP文档注释标记
        $patterns = [
            '/^[ \t]{0,}\/\*\*\s{0,}/', // 删除 "/**"
            '/\s{0,}\*\/$/', // 删除 " */"
            '/^[ \t]{0,}\*[ \t]{0,}/m', // 删除每行头部的 " * "
        ];
        $replaces = ['', '', ''];
        $result = preg_replace($patterns, $replaces, $text);

        return $result;
    }

    /**
     * 解析一个文档注释的文本为一个结构化的nodes
     *
     * @param string $text
     *
     * @return array 解析好的nodes
     */
    public function parseDocCommentText($text)
    {
        // 把 PHPDoc 文档的内容按照 @xxxxx 拆分成若干段
        $ptn = "/(@[a-zA-Z_-]{1,})\s{1,}/";
        $items = preg_split($ptn, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        // 根据items, 生成nodes
        $nodes = [];
        $defaultNode = [
            "tag"  => "", // tag
            "desc" => '', // desc
        ];
        $node = $defaultNode;
        foreach ($items as $item) {
            $re = "/^@[a-zA-Z_-]{1,}$/";
            if (preg_match($re, $item)) {
                // 上个node可以结束
                if ($node != $defaultNode) {
                    $nodes[] = $node;
                }

                // 开始一个新的node
                $node = [
                    'tag'  => mb_substr($item, 1),
                    'desc' => '',
                ];
            } else {
                if ($node['desc'] === '') {
                    $node['desc'] = $item;
                } else {
                    $node['desc'] .= "\n" . $item;
                }
            }
        }

        // 处理最后一个node
        if ($node != $defaultNode) {
            $nodes[] = $node;
        }

        // 删除行尾的空白, 包括换行符
        foreach ($nodes as &$node) {
            // 删除每行的行尾空白
            $node["desc"] = preg_replace("/\s{1,}$/m", '', $node["desc"]);

            // 删除最后N个空白行, 包括换行符
            $node["desc"] = preg_replace("/\s{1,}$/", '', $node["desc"]);
        }

        // 常用pattern
        $ptnType = "[A-Za-z\\][A-Za-z0-9_\\|]{0,}"; // 变量类型
        $ptnSpace0 = "[ \t]{0,}"; // 0个或者多个空格
        $ptnSpace1 = "[ \t]{1,}"; // 1个或者多个空格
        $ptnVar = "\\$[A-Za-z_][A-Za-z0-9_]{0,}"; // 变量
        $ptnAny = ".{0,}"; // 任意内容

        // 根据tag对text进行进一步解析
        foreach ($nodes as &$node) {
            switch ($node['tag']) {
                case 'param':
                case 'var':
                    //        |----1---|          |---2---|          |---3---|
                    $re1 = "/^($ptnType)$ptnSpace1($ptnVar)$ptnSpace0($ptnAny)/";
                    $matches = [];
                    if (preg_match($re1, $node["desc"], $matches)) {
                        unset($node["desc"]);
                        $node["type"] = $matches[1];
                        $node["name"] = $matches[2];
                        $node["desc"] = $matches[3];
                    } else {
                        // 不正确的param
                        $node["tag"] = "-" . $node["tag"];
                    }
                    break;

                case 'return':
                    //        |----1---|          |---2---|
                    $re1 = "/^($ptnType)$ptnSpace0($ptnAny)/";
                    $matches = [];
                    if (preg_match($re1, $node["desc"], $matches)) {
                        unset($node["desc"]);
                        $node["type"] = $matches[1];
                        $node["desc"] = $matches[2];
                    } else {
                        // 不正确的param
                        $node["tag"] = "-" . $node["tag"];
                    }
                    break;
            }
        }

        // 保存生成的nodes
        return $nodes;
    }

    /**
     * 获取所有nodes
     *
     * @return array
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * 筛选所有tag节点
     *
     * @param string $tag
     *
     * @return array 返回满足条件的nodes集合
     */
    public function getNodesByTag($tag)
    {
        $result = [];

        foreach ($this->node as $node) {
            if ($node['tag'] === $tag) {
                $result[] = $node;
            }
        }

        return $result;
    }

    /**
     * 根据参数名, 获取参数节点. 如果对应的参数不存在, 返回null.
     *
     * @param string $name
     *
     * @return array|null
     */
    public function getParamNode($name)
    {
        foreach ($this->nodes as $node) {
            // 如果不是param节点, 查下一个
            if ($node['tag'] !== 'param') {
                continue;
            }

            // 如果paramName符合, 返回这个节点
            if ($node['name'] === $name) {
                return $node;
            }
        }

        // 没有找到, 返回null
        return null;
    }

    /**
     * 返回return节点. 如果对应节点不存在, 返回null.
     *
     * @return array|null
     */
    public function getReturnNode()
    {
        foreach ($this->nodes as $node) {
            // 如果不是param节点, 查下一个
            if ($node['tag'] !== 'return') {
                continue;
            }

            // 返回这个节点
            return $node;
        }

        // 没有找到, 返回null
        return null;
    }
}
