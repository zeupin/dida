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
            echo "@method static $s\n";
        }
    }

    public function buildMethod(ReflectionMethod $method)
    {
        $name = $method->getName();

        $params = [];
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $params[] = $this->buildParameter($parameter);
        }
        $params = implode(", ", $params);

        return "$name($params)";
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
