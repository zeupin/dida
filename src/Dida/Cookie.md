# Cookie

## 方法

初始化设置：

- `setSafeKey($safeKey)` -- 设置安全密钥

- `setValidPath($validPath)` -- 设置有效路径

  > 注：`setValidPath()` 和 `setValidDomain()`，见下面关于 `Cookie 的路径问题` 的说明。

- `setValidDomain($validDomain)` -- 设置有效域名

  > Cookie 的有效域名/子域名。设置成子域名（例如 'www.example.com'），会使 Cookie 对这个子域名和它的三级域名有效（例如 w2.www.example.com）。要让 Cookie 对整个域名有效（包括它的全部子域名），只要设置成域名就可以了（这个例子里是 'example.com'）。旧版浏览器仍然在使用废弃的 RFC 2109，需要一个前置的点 `.` 来匹配所有子域名。

使用：

- `set($name, $value, $expires = 0, $secure = false, $httponly = false)` -- 设置 cookie

- `get($name)` -- 获取指定 cookie

- `getAll()` -- 获取全部 cookie

- `remove($name)` -- 删除指定 cookie

- `clear()` -- 清除全部 cookie

- `getNames()` -- 获取所有 cookie 名

## Cookie 的路径问题

cookie 的 (name + path + domain) 一起，共同形成的完整的 cookie 可访问路径，类似于 FQCN。

因此，查看浏览器的 Cookie 列表，可以看到如下记录：

| name | path | value |
| ---- | ---- | ----- |
| a    | /    | 100   |
| b    | /    | 200   |
| a    | /oa  | 300   |
| b    | /oa  | 400   |

这表明，对于同一个域名下的网站，是可能有多个同名 cookie 存在的，只要它们的 path 不同就行。

因此，在本 `Cookie类` 中，所有调用 php 内置函数 `setcookie()` 的地方，都强制设置：

```php
$path = self::$validPath;
$domain = self::$validDomain;
```

## Cookie 加密

1. `self::$safeKey` 为空串时，表示不启用加密机制。<br>`self::$safeKey` 为非空时，表示启用 cookie 值加密机制。

1. 对于某个 `cookie[$name => $value]`，实际加密密钥为 `self::$safeKey + $name`。经这样处理后，两个 cookie 即使原值相同，它们加密后的值也不会相同，更安全。
