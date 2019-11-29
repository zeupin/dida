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

## cookie 中点号(句号)会自动转为下划线的问题

如果有个如下设置：

```php
Cookie::set("a.b", "1000");
```

在读取 `$_COOKIE` 时，会发现，根本找不到 `a.b` 这个 cookie 项，而是多了个 `a_b` 这个 cookie 项。

网上查下来，原因是 PHP 在生成 `$_COOKIE` 变量时，会将 cookie 名中的句点全部替换成下划线，这是 PHP 干的。

另外，如果想用遍历 `$_COOKIE` 的方式删除所有 cookie 项，会发现 `a_b` cookie 项一直删除不掉，因为实际浏览器上保存的键值是 `a.b`。必须要用 `Cookie::set("a.b", '')` 的方式来删除。

结论是：别自己找麻烦，**绝对不要在 cookie 名字中包含有任何小数点**。

## 关于 Cookie 的知识

总的来说，Cookie 是种逐渐被淘汰的过气技术。除非客户强烈要求支持老款 IE6-IE8 的浏览器，否则不建议使用 Cookie，可以考虑用 `Local Stoage`, `Indexed DB` 等 HTML5 技术来代替。

### Cookie 的作用

以下内容为 MSN 上对 Cookie 的说明，表述很清晰。

> HTTP Cookie（也叫 Web Cookie 或浏览器 Cookie）是服务器发送到用户浏览器并保存在本地的一小块数据，它会在浏览器下次向同一服务器再发起请求时被携带并发送到服务器上。通常，它用于告知服务端两个请求是否来自同一浏览器，如保持用户的登录状态。Cookie 使基于无状态的 HTTP 协议记录稳定的状态信息成为了可能。
>
> Cookie 主要用于以下三个方面：
>
> - 会话状态管理（如用户登录状态、购物车、游戏分数或其它需要记录的信息）
> - 个性化设置（如用户自定义设置、主题等）
> - 浏览器行为跟踪（如跟踪分析用户行为等）
>
> Cookie 曾一度用于客户端数据的存储，因当时并没有其它合适的存储办法而作为唯一的存储手段，但现在随着现代浏览器开始支持各种各样的存储方式，Cookie 渐渐被淘汰。由于服务器指定 Cookie 后，浏览器的每次请求都会携带 Cookie 数据，会带来额外的性能开销（尤其是在移动环境下）。新的浏览器 API 已经允许开发者直接将数据存储到本地，如使用 Web storage API （本地存储和会话存储）或 IndexedDB 。

## 参考资料

1. MDN 上关于 Cookie 的 HTTP 规范 <https://developer.mozilla.org/zh-CN/docs/Web/HTTP/Cookies>
