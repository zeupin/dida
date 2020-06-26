# `Dida\Application::run()`

当执行 `Dida\Application::run()` 时, 会首先加载本目录中的`常量定义文件`(`constants.php`) 和`系统函数文件`(`functions.php`).

constants.php

```php
define('DS', DIRECTORY_SEPARATOR);
```

functions.php

```php
```