<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Make;

use \ReflectionClass;
use \ReflectionMethod;
use \ReflectionParameter;

class Facade
{
    public function facade($classname)
    {
        $refClass = new ReflectionClass($classname);
        $methods = $refClass->getMethods(ReflectionMethod::IS_PUBLIC + ReflectionMethod::IS_STATIC);
        echo "/*\n";
        echo " * Facade methods for $classname\n";
        echo " *\n";
        foreach ($methods as $method) {
            $s = $this->buildMethod($method);
            echo " * @method static $s\n";
        }
        echo " */\n";
    }

    public function buildMethod(ReflectionMethod $method)
    {
        // 方法名
        $name = $method->getName();

        // 参数
        $parameters = $method->getParameters();

        // 命名空间
        $namespace = $method->getNamespaceName();

        // PHPDoc
        $comment = $method->getDocComment();
        $docComment = new DocComment($comment);

        // 构造参数
        $params = [];
        foreach ($parameters as $parameter) {
            $paramName = "$" . $parameter->getName();
            $paramNode = $docComment->getParamNode($paramName);
            if ($paramNode === null) {
                $commentType = null;
            } else {
                $commentType = $paramNode["type"];
            }
            $params[] = $this->buildParameter($parameter, $commentType);
        }
        $params = implode(", ", $params);

        // 构造返回类型
        $returnNode = $docComment->getReturnNode();
        if ($returnNode === null) {
            return "$name($params)";
        } else {
            return $returnNode["type"] . " $name($params)";
        }
    }

//
//    public function parseMethodDocComment($comment, $namespace)
//    {
//        $patterns = [
//            '/^\/\*\*\s{0,}/', // 删除 "/**"
//            '/\s{0,}\*\/$/', // 删除 " */"
//            '/[ \t]{0,}\*[ \t]{0,}/' // 删除 " * "
//        ];
//        $result = preg_replace($patterns, '', $comment);
//
//        // 把结果分解成行
//        $lines = preg_split("/\n\r{0,1}/", $result);
//
//        // 最终生成的nodes
//        $nodes = [];
//
//        // 默认的起始节点
//        $defaultNode = [
//            'tag'  => null,
//            'text' => '',
//        ];
//        $node = $defaultNode;
//
//        // 解析每行
//        foreach ($lines as $line) {
//            // 检查本行是否是 @xxxx 模式
//            //         |-----1------|         |--2--|
//            $re = "/^\@([a-zA-Z]{1,})[ \t]{0,}(.{0,})/";
//            $matches = [];
//            if (preg_match($re, $line, $matches)) {
//                $tag = $matches[1];  // tag
//                $rest = $matches[2]; // 剩余部分
//                // 把上一个node结束掉
//                // 如果上个node为默认的起始节点,或者为无效节点,则忽略
//                if ($node != $defaultNode && $node !== null) {
//                    $nodes[] = $node;
//                }
//
//                // 开始当前node
//                $node = [
//                    'tag' => $tag,
//                ];
//
//                // 按照不同类型解析
//                switch ($tag) {
//                    case "param":
//                        $result = preg_split("/[ \t]{1,}/", $rest, 3);
//                        // 无法解析
//                        if (count($result) < 2) {
//                            $node = null;
//                            continue;
//                        }
//
//                        // 设置param
//                        $node["type"] = $result[0];
//                        $node["name"] = $result[1];
//                        if (isset($result[2])) {
//                            $node["text"] = $result[2];
//                        } else {
//                            $node["text"] = '';
//                        }
//                        break;
//
//                    case "return":
//                        $result = preg_split("/[ \t]{1,}/", $rest, 2);
//                        $node["type"] = $result[0];
//                        if (isset($result[1])) {
//                            $node["text"] = $result[1];
//                        } else {
//                            $node["text"] = '';
//                        }
//                        break;
//
//                    default:
//                        // 去掉$rest的头部和尾部空白
//                        $re = [
//                            "/^\s{1,}/", // 去掉头部空白
//                            "/\s{1,}$/", // 去掉尾部空白
//                        ];
//                        $node["text"] = preg_replace($re, '', $rest);
//                }
//            } else {
//                if ($node === null) {
//                    // 上个node非法
//                } else {
//                    // 把当前行添加到 node[text]
//                    if (($node["text"] === '')) {
//                        $node["text"] = $line;
//                    } else {
//                        $node["text"] .= "\n" . $line;
//                    }
//                }
//            }
//        }
//
//        // 把上一个node结束掉
//        if ($node != $defaultNode) {
//            $nodes[] = $node;
//        }
//
//        // 最后整理一下
//        foreach ($nodes as $index => $node) {
//            // 剔除掉每个node的尾部空白
//            $node["text"] = preg_replace("/\s{1,}$/", '', $node["text"]);
//
//            // 对类型进行处理
//            if (isset($node["type"])) {
//                $node["type"] = $this->parseType($node["type"], $namespace);
//            }
//
//            // 保存
//            $nodes[$index] = $node;
//        }
//
//        // 返回解析后的DocDocument的nodes
//        // var_dump($nodes);
//        return $nodes;
//    }

    public function parseType($type, $namespace)
    {
        $result = [];
        $types = explode("|", $type);
        foreach ($types as $type) {
            switch ($type) {
                case "void":
                case "string":
                case "array":
                case "callable":
                case "mixed":
                case "bool":
                case "boolean":
                case "true":
                case "false":
                case "null":
                case "int":
                case "integer":
                case "float":
                case "double":
                    $result[] = $type;
                    break;
                default:
                    if (preg_match('/^\[A-Za-z_]/', $type)) {
                        // 绝对命名空间
                        $result[] = $type;
                    } elseif (preg_match('/^[A-Za-z_]/', $type)) {
                        // 加上namespace
                        $result[] = $namespace . "\\$type";
                    } else {
                        // 如有古怪字符
                        $result[] = $type;
                    }
            }
        }
        return implode("|", $result);
    }

    public function buildParameter(ReflectionParameter $parameter, $commentType = null)
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
        } elseif (is_string($commentType)) {
            $s[] = $commentType;
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
            // 如果默认值是个常量, 返回这个常量名
            if ($parameter->isDefaultValueConstant()) {
                $s[] = $parameter->getDefaultValueConstantName();
            } else {
                $s[] = $this->buildValue($parameter->getDefaultValue());
            }
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
