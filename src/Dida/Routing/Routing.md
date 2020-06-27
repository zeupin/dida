1. [Router 的主要工作](#router-的主要工作)
2. [路由表](#路由表)
   1. [典型的 Http 路由表文件](#典型的-http-路由表文件)
   2. [典型的 Console 路由表文件](#典型的-console-路由表文件)
   3. [说明](#说明)

# Router 的主要工作

1. **match()**

   - 把给出的 `pathinfo`，按照自定义的路由表(`Routing Table`)或者路由规则(`Routing Rules`), 确定能否解析到一个路由(`Route`) 。
   - `Route` 形式上是一个数组, 包含如下三个字段 `path`, `callback`, `parameters`。
   - 其中 `path` 可能是 `pathinfo`, 也可能是 `pathinfo` 的子集。

2. **check()**

   形式上检查, 由 `match()` 得到的 `Route` 是否可执行。

3. **execute()**

# 路由表

## 典型的 Http 路由表文件

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

## 典型的 Console 路由表文件

```php
return [
    /* Home */
    "" => ["\\Biz\\HomeController", "index"], // 兜底，一般显示命令帮助

    /* User 管理 */
    "user add"     => ["\\Biz\\UserController", "add"],
    "user remove"  => ["\\Biz\\UserController", "remove"],
];
```

## 说明

每条记录，`key` 对应的是路由路径，`value` 对应的是 `callback` 形态的路由目标函数。

> 虽然 callback 的目标函数有很多种形式，比如 `函数名`、`匿名函数`、`[控制器名, 方法名]`、`[实例, 方法名]` 等等。但从以往我们的编程实践看，炫技式的 callback 非常不利于代码的长期维护。因此，从软件工程的角度考虑，Dida 框架**强烈建议**只使用 `[控制器名, 方法名]` 这种形式的路由目标函数声明。同时，为进一步简化模型，对于上述路由目标函数，建议函数不要带任何参数。举个例子：
>
> - 推荐: `public function foo(){...}`
> - 不推荐: `public function foo(`<del>\$param1, \$param2, ...</del>`){...}`

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
