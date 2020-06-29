<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

/**
 * Refleaction
 */
class Make
{
    public function facade($classname)
    {
        $refClass = new ReflectionClass($classname);
        $methods = $refClass->getMethods(ReflectionMethod::IS_PUBLIC + ReflectionMethod::IS_STATIC);
        foreach ($methods as $method) {
            $s = $this->buildMethod($method);
            // echo "@method static $s\n";
        }
    }

    public function buildMethod(ReflectionMethod $method)
    {
        $name = $method->getName();
        $comment = $method->getDocComment();
        $this->parseMethodDocComment($comment);

        $params = [];
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $params[] = $this->buildParameter($parameter);
        }
        $params = implode(", ", $params);

        return "$name($params)";
    }

    public function parseMethodDocComment($comment)
    {
        $patterns = [
            '/^\/\*\*\s{0,}/',
            '/\s{0,}\*\/$/',
            '/[ \t]{0,}\*[ \t]{0,}/'
        ];
        $result = preg_replace($patterns, '', $comment);
        $lines = preg_split("/\n\r{0,1}/", $result);

        $nodes = [];
        $defaultNode = [
            'tag'     => null,
            'text'    => '',
        ];
        $node = $defaultNode;
        foreach ($lines as $line) {
            $re = "/^\@([a-zA-Z]{1,})[ \t]{0,}(.{0,})/";
            $matches = [];
            if (preg_match($re, $line, $matches)) {
                $tag = $matches[1];
                $rest = $matches[2]; // 剩余部分

                // 把上一个node结束掉
                if ($node != $defaultNode && $node !== null) {
                    $nodes[] = $node;
                }

                // 开始这个node
                $node = [
                    'tag'  => $tag,
                ];

                var_dump($rest);
                // 按照类型解析
                switch ($tag) {
                    case "param":
                        $result = preg_split("/[ \t]{1,}/", $rest, 3);
                        // 无法解析
                        if (count($result) < 2) {
                            $node = null;
                            continue;
                        }

                        // 设置param
                        $node["type"] = $result[0];
                        $node["name"] = $result[1];
                        if (isset($result[2])) {
                            $node["text"] = $result[2];
                        } else {
                            $node["text"] = '';
                        }
                        break;

                    case "return":
                        $result = preg_split("/[ \t]{1,}/", $rest, 2);
                        $node["type"] = $result[0];
                        if (isset($result[1])) {
                            $node["text"] = $result[1];
                        } else {
                            $node["text"] = '';
                        }
                        break;
                }
            } else {
                if ($node === null) {
                    // 上个node非法
                } else {
                    $node["text"] .= (($node["text"]) ? "\n" : '') . $line;
                }
            }
        }

        // 把上一个node结束掉
        if ($node != $defaultNode) {
            $nodes[] = $node;
        }

        var_dump($nodes);
    }

    public function buildParameter(ReflectionParameter $parameter)
    {
        // 开始
        $s = [];

        // ----------------------------------------------------------------

        if ($parameter->isArray()) {
            // 如果是array
            $s[] = "array";
        } elseif ($parameter->isCallable()) {
            // 如果是callable
            $s[] = "callable";
        }

        if ($parameter->getClass()) {
        }

        // ----------------------------------------------------------------
        // 是否是引用
        $t = ($parameter->isPassedByReference()) ? "&" : '';

        // 参数名
        $t .= "$" . $parameter->getName();
        $s[] = $t;

        // ----------------------------------------------------------------
        // 如果有缺省值
        if ($parameter->isDefaultValueAvailable()) {
            $s[] = "=";
            $s[] = $this->buildValue($parameter->getDefaultValue());
        }

        // ----------------------------------------------------------------

        $final = implode(" ", $s);
        return $final;
    }

    public function buildValue($value)
    {
        $typename = gettype($value);
        switch ($typename) {
            case "boolean":
            case "integer":
            case "float":
            case "double":
            case "string":
            case "object":
                return var_export($value, true);
            case "NULL":
                return "null";
            case "array":
                $s = [];
                foreach ($value as $k => $v) {
                    if (is_int($k)) {
                        $s[] = $this->buildValue($v);
                    } else {
                        $s[] = "'$k' => " . $this->buildValue($v);
                    }
                }
                return '[' . implode(", ", $s) . ']';
            case "resource":
                return var_export($value, true);
            case "unknown type":
                throw new Exception("Unknown type.");
        }
    }
}
