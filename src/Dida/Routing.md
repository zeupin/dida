# Routing 路由

Routing 的主要工作是：

1. 把给出的 `path` ，按照路由表(Routing Table)，解析为 `[controller, action]`。
2. 检查 `controller->action` 是否存在？
3. 如果 `controller->action` 存在，则执行 `controller->action` 操作。

`Routing` 只负责从 `path` 中解析出 `controller` 和 `action` ，而：

1. 不负责读取或者处理 `controller` 或者 `action` 的具体执行参数（`parameters`）。
   > 读取和处理 `parameters` 属于业务代码范畴，应该在 `controller` 或者 `action` 里面去完成。
2. 不负责检查用户的执行权限。
   > 检查执行权限属于业务代码范畴，应该在 `controller` 或者 `action` 里面中去完成。
3. 不负责从 `$_SERVER["REQUEST_URI"]` 解析出 `path`。
   > 这么做主要是为了兼容命令行模式(CLI)。
   > Web 模式下，把 `$_SERVER["REQUEST_URI"]` 解析出 Routing 需要的 `path`，一般是在 `DIDA_APP_DIR` 的入口程序中完成，方法是调用 `Request::getPathOffset()`。

一个典型的路由表文件如下：

```php
return [
    /* Home */
    ""             => ["\\Biz\\HomeController", "index"], // 默认首页
    "/home/index"  => ["\\Biz\\HomeController", "index"],
    "/home/duty"   => ["\\Biz\\HomeController", "duty"],

    /* Login */
    "/login"       => ["\\Biz\\LoginController", "login"],
    "/logout"      => ["\\Biz\\LoginController", "logout"],
    "/login/proc"  => ["\\Biz\\LoginController", "proc"],
    "/logout/proc" => ["\\Biz\\LoginController", "proc"],

    /* User */
    "/user/list"   => ["\\Biz\\UserController", "index"],
    "/user/add"    => ["\\Biz\\UserController", "add"],
    "/user/update" => ["\\Biz\\UserController", "update"],
    "/user/delete" => ["\\Biz\\UserController", "delete"],
];
```

每条记录，`键名`对应的是路由路径，`键值`对应的是 callback 形态的路由目标函数。

虽然 callback 的目标函数有很多种形式，比如 `函数名`、`匿名函数`、`[类名, 方法名]`、`[实例, 方法名]` 等等。但从以往我们的编程实践看，炫技式的 callback 非常不利于代码的长期维护。因此，从软件工程的角度考虑，Dida 框架目前只支持 `[类名, 方法名]` 这种形式的路由目标函数声明。

同时，为进一步简化模型，对于上述路由目标函数，建议函数不要带任何参数。举个例子：应为 `public function foo(){...}`, 而不要是 `public function foo(`<del>\$bar</del>`){...}`。

一般是将 路由目标函数 指向到 `Controller` 的 `action` ：

```php
/*
  上面路由表的
  "/user/add" => ["\\Biz\\UserController", "add"],
  指向的是就是如下的 add() 方法
 */

namespace Biz;

class UserController
{
    public function add()
    {
        // 定义
    }
}
```
