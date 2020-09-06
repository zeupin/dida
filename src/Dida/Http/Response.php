<?php
/**
 * Dida Framework  -- PHP轻量级快速开发框架
 * 版权所有 (c) 上海宙品信息科技有限公司
 *
 * Github: <https://github.com/zeupin/dida>
 * Gitee: <https://gitee.com/zeupin/dida>
 */

namespace Dida\Http;

/**
 * Response
 */
class Response
{
    /**
     * Version
     */
    const VERSION = '20200905';

    /**
     * 告知浏览器不要缓存
     *
     * 1. 如果在 Cache-Control 响应头设置了 "max-age" 或者 "s-max-age" 指令，那么 Expires 头会被忽略。
     *    <https://developer.mozilla.org/zh-CN/docs/Web/HTTP/Headers/Expires>
     *
     * @return void
     */
    public function disableCache()
    {
        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate'); // HTTP 1.1
        header('Pragma: no-cache'); // HTTP 1.0
        header('Expires: 0'); // see 1
    }

    /**
     * 设置允许跨域资源共享(CORS)
     *
     * @return void
     */
    public function allowCORS()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
    }

    /**
     * 批量设置应答的 HTTP Header
     *
     * @param array $headers
     *
     * @return void
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $header) {
            if (is_int($key)) {
                header($header);
            } else {
                header("$key: $header");
            }
        }
    }

    /**
     * 输出一个json应答
     *
     * $options参数设置：
     *     cache
     *         不设置  默认禁用缓存
     *         "default" 默认缓存
     *     headers(或header，但是都要用如下二维数组形式)
     *         [
     *             "key1" => "value1", // 例如 header('Expires: 0')
     *             "key2" => "value2", // header('key2: value2')
     *         ]
     *     pretty_print
     *         true或1  输出格式化的json
     *     json_encode_options
     *         int  如果设置了这个值，则直接在json_encode中使用
     *
     * @param mixed $data
     * @param array $options 输出设置
     *
     * @return void
     */
    public function json($data, array $options = [])
    {
        // cache处理
        // 如果没有设置cache，默认是禁用cache
        if (array_key_exists("cache", $options)) {
            switch ($options["cache"]) {
                case "default":
                    break;
                default:
                    $this->disableCache();
            }
        } else {
            $this->disableCache();
        }

        // header/headers处理
        if (array_key_exists("header", $options)) {
            $this->setHeaders($options["header"]);
        }
        if (array_key_exists("headers", $options)) {
            $this->setHeaders($options["headers"]);
        }

        // json_encode
        $json_options = JSON_UNESCAPED_UNICODE;

        // pretty_print
        if (array_key_exists("pretty_print", $options) && $options["pretty_print"]) {
            $json_options = $json_options | JSON_PRETTY_PRINT;
        }

        // json_encode_options
        if (array_key_exists("json_encode_options", $options)) {
            $json_options = $options["json_encode_options"];
        }

        // 输出 json
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode($data, $json_options);
    }

    /**
     * 重定向
     *
     * @param string     $url
     * @param array|null $cacheSetting 缓存设置
     *
     * @return void
     */
    public function redirect($url, $cacheSetting = null)
    {
        // 缓存设置
        if ($cacheSetting === null) {
            $this->disableCache();
        } elseif (is_array($cacheSetting)) {
            $this->setHeaders($cacheSetting);
        }

        // 执行
        header("Location: $url");
    }

    /**
     * 输出一个文件下载。
     *
     * @param string     $srcfile      服务器上源文件的文件名。
     * @param string     $name         下载时的文件名。如果为null，则默认使用srcfile的文件名。
     * @param bool       $mime         是否需要设置文件的MIME。
     * @param array|null $cacheSetting 缓存设置
     *
     * @return bool
     */
    public function download($srcfile, $name = null, $mime = false, $cacheSetting = null)
    {
        // 检查待下载的源文件是否存在。
        if (file_exists($srcfile)) {
            $realfile = $srcfile;
        } else {
            // 检查是否是因为文件名的中文编码导致的文件没有找到
            // linux的文件名编码默认是utf-8
            // windows的文件名编码很混乱，有的是utf8，有的是GBK
            $realfile = iconv('UTF-8', 'GBK', $srcfile);

            // 如果文件还是没有找到，则说明文件真不存在
            if (!file_exists($realfile)) {
                return false;
            }
        }

        // 下载时的文件名。
        // 本来是用PHP自带basename()函数，但是basename()处理中文文件名时要先setlocale(LC_ALL, 'PRC')，
        // 不然会处理错误。而setlocale()函数不是所有服务器都支持，所以换成用如下代码填坑。
        if (!is_string($name)) {
            $name = $srcfile;
        }
        $name = str_replace('\\', '/', $name);
        $basename = mb_strrchr($name, '/');
        if ($basename) {
            $name = mb_substr($basename, 1);
        }

        // 对下载文件名按照RFC3896进行rawurlencode编码，以支持中文文件名。
        $name = rawurlencode($name);

        // 如果需要自动设置mime，调用php内置的mime_content_type()函数来处理。
        if ($mime) {
            $mimetype = mime_content_type($realfile);
        } else {
            $mimetype = 'application/force-download';
        }

        // 文件大小
        $filesize = filesize($realfile);

        // 缓存设置
        if ($cacheSetting === null) {
            $this->disableCache();
        } elseif (is_array($cacheSetting)) {
            $this->setHeaders($cacheSetting);
        }

        // 设置输出报头
        header("Content-Type: $mimetype");
        header("Content-Disposition: attachment; filename*=\"$name\"");
        header("Content-Length: $filesize");

        // 输出内容
        ob_clean();
        flush();
        readfile($realfile);

        // 结束
        return true;
    }
}
