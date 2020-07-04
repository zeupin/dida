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
        $ret = [];
        $refClass = new ReflectionClass($classname);
        $methods = $refClass->getMethods(ReflectionMethod::IS_PUBLIC + ReflectionMethod::IS_STATIC);
        $ret[] = "/*";
        $ret[] = " * Facade methods for $classname";
        $ret[] = " *";
        foreach ($methods as $method) {
            $s = $this->buildMethod($method);
            $ret[] = " * @method static $s";
        }
        $ret[] = " */";

        return implode("\n", $ret) . "\n";
    }

    /**
     * 按照模板生成一个Facade,并写入到指定文件
     *
     * @param string $originalClassname 参照的类名
     * @param string $facadeClassname   Facade的类名
     * @param string $namespace         Facade的namespace
     * @param string $serviceName       绑定的服务名
     * @param string $outputFile        输出文件路径
     */
    public function buildFacade($originalClassname, $facadeClassname, $namespace, $serviceName, $outputFile)
    {
        // 生成
        $refClass = new ReflectionClass($originalClassname);

        // 伪方法
        $methods = $refClass->getMethods(ReflectionMethod::IS_PUBLIC + ReflectionMethod::IS_STATIC);
        $ret = [];
        $ret[] = "/*";
        $ret[] = " * Facade methods for $originalClassname";
        $ret[] = " *";
        foreach ($methods as $method) {
            $s = $this->buildMethod($method);
            $ret[] = " * @method static $s";
        }
        $ret[] = " */";
        $methods = implode("\n", $ret);

        // namespace部分
        if ($namespace === '' || $namespace === '\\') {
            $namespace = '';
        } else {
            $namespace = "namespace $namespace;\n";
        }

        // 输出文件的模板
        $tpl = <<<TEMPLATE
<?php
$namespace
use \Dida\Facade;

$methods
class $facadeClassname extends Facade
{
    protected static function setFacadeServiceLink()
    {
        static::\$facadeServiceLink = ["$serviceName", Facade::TYPE_SERVICE_BUS, [], false];
    }
}

TEMPLATE;

        // 输出到目标文件
        file_put_contents($outputFile, $tpl);
    }

    /**
     * 生成method的定义
     *
     * @param \ReflectionMethod $method
     *
     * @return string
     */
    public function buildMethod(\ReflectionMethod $method)
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

    /**
     * 解析一个类型串
     *
     * @param string $type      类型表达式. 如果是复合类型,中间用 | 分割.类型串中不可含有空格
     * @param string $namespace 命名空间
     *
     * @return string
     */
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
                        // 绝对命名空间, 以 \ 开头
                        // 所以说,在代码中尽量以这个形式给出参数,定义最清晰
                        $result[] = $type;
                    } elseif (preg_match('/^[A-Za-z_]/', $type)) {
                        // 相对, 以字母开头, 需要额外加上 namespace
                        // todo: 还应该加上 use Xxxx\Yxxxx 的定义
                        $result[] = $namespace . "\\$type";
                    } else {
                        // 如果无法识别,就按照原样输出
                        $result[] = $type;
                    }
            }
        }
        return implode("|", $result);
    }

    /**
     * 生成parameter字符串
     *
     * @param \ReflectionParameter $parameter
     * @param string               $commentType
     *
     * @return string
     */
    public function buildParameter(\ReflectionParameter $parameter, $commentType = null)
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

            if ($parameter->isDefaultValueConstant()) {
                // 如果默认值是个常量, 返回这个常量名
                // 如果常量是类常量, 返回格式为: 命名空间\类名::常量名
                $s[] = $parameter->getDefaultValueConstantName();
            } else {
                // 如果是个数值, 返回这个数值
                $s[] = $this->buildValue($parameter->getDefaultValue());
            }
        }

        // ----------------------------------------------------------------

        $final = implode(" ", $s);
        return $final;
    }

    /**
     * 生成value的表达式
     *
     * @param string $value 值
     *
     * @return string
     *
     * @throws Exception 如果是无法识别的value表达式,抛异常
     */
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
