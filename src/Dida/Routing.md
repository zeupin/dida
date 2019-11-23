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
